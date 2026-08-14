<?php

namespace App\Services\Agent\Tools;

use App\Actions\Examination\UpsertMarks;
use App\Models\ExamSubject;
use App\Models\Student;
use App\Models\User;
use App\Services\Agent\AgentScope;
use App\Services\Audit\ActivityLogger;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EnterMarksTool extends AbstractAgentTool
{
    public function __construct(
        private AgentScope $scope,
        private UpsertMarks $upsertMarks,
        private ActivityLogger $activityLogger,
    ) {}

    public function name(): string
    {
        return 'enter_marks';
    }

    public function description(): string
    {
        return 'Enter or replace marks for one exam subject. Teachers may only enter marks they are allowed to. Records are {student (name or admission_no), marks_obtained}. Cannot edit published exams.';
    }

    public function parameters(): array
    {
        return $this->objectSchema([
            'exam_name' => $this->stringParam('Exam name.'),
            'subject_name' => $this->stringParam('Subject name or code on that exam.'),
            'replace_all' => $this->booleanParam('Replace every mark for this subject (default true).'),
            'records' => $this->arrayParam('Mark rows.', $this->objectSchema([
                'student' => $this->stringParam('Name or admission number.'),
                'marks_obtained' => $this->integerParam('Marks obtained.'),
            ], ['student', 'marks_obtained'])),
        ], ['exam_name', 'subject_name', 'records']);
    }

    public function authorized(User $user): bool
    {
        return $this->scope->canViewMarks($user);
    }

    public function handle(User $user, array $arguments): array
    {
        $examName = $this->stringArg($arguments, 'exam_name');
        $subjectName = $this->stringArg($arguments, 'subject_name');

        if ($examName === null || $subjectName === null) {
            return ['ok' => false, 'error' => 'exam_name and subject_name are required.'];
        }

        $exam = $this->scope->resolveExam($user, $examName)->load('examSubjects.subject');
        $search = Str::lower($subjectName);
        $examSubject = $exam->examSubjects->first(function (ExamSubject $row) use ($search): bool {
            $name = Str::lower((string) $row->subject?->name);
            $code = Str::lower((string) $row->subject?->code);

            return $name === $search || $code === $search || Str::contains($name, $search);
        });

        if ($examSubject === null) {
            throw ValidationException::withMessages([
                'subject_name' => __('That subject is not on exam :name.', ['name' => $exam->name]),
            ]);
        }

        Gate::forUser($user)->authorize('enterMarks', $examSubject);

        $eligible = $exam->eligibleStudents();
        $records = [];

        foreach ($this->arrayArg($arguments, 'records') as $row) {
            if (! is_array($row)) {
                continue;
            }

            $lookup = is_string($row['student'] ?? $row['admission_no'] ?? null)
                ? trim((string) ($row['student'] ?? $row['admission_no']))
                : '';
            $studentId = isset($row['student_id']) && is_numeric($row['student_id']) ? (int) $row['student_id'] : null;
            $marks = $row['marks_obtained'] ?? null;

            if (! is_numeric($marks)) {
                throw ValidationException::withMessages([
                    'records' => __('Each record needs marks_obtained.'),
                ]);
            }

            if ($studentId !== null) {
                $student = $eligible->first(
                    fn (Student $candidate): bool => $candidate->id === $studentId,
                );
            } elseif ($lookup !== '') {
                $student = $this->scope->pickStudent($eligible, $lookup);
            } else {
                throw ValidationException::withMessages([
                    'records' => __('Each record needs a student name, admission number, or student_id.'),
                ]);
            }

            if ($student === null) {
                throw ValidationException::withMessages([
                    'records' => __('All students must be eligible for this exam.'),
                ]);
            }

            $records[] = [
                'student_id' => $student->id,
                'marks_obtained' => $marks,
            ];
        }

        if ($records === []) {
            return ['ok' => false, 'error' => 'records are required.'];
        }

        $examSubject = $this->upsertMarks->handle(
            $examSubject,
            $records,
            $user->teacher,
            $this->boolArg($arguments, 'replace_all') ?? true,
        );

        $this->logMutation(
            $this->activityLogger,
            $user,
            __('SMIS Agent entered marks for :subject on :exam.', [
                'subject' => $examSubject->subject->name,
                'exam' => $exam->name,
            ]),
            $examSubject,
        );

        return [
            'ok' => true,
            'exam' => $exam->name,
            'subject' => $examSubject->subject?->name,
            'saved' => count($records),
        ];
    }
}
