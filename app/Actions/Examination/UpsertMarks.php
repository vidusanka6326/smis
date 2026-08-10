<?php

namespace App\Actions\Examination;

use App\Models\ExamSubject;
use App\Models\Mark;
use App\Models\Teacher;
use App\Services\Examination\MarksResultCalculator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpsertMarks
{
    public function __construct(private MarksResultCalculator $calculator) {}

    /**
     * @param  list<array{student_id: int, marks_obtained: float|int|string}>  $records
     */
    public function handle(ExamSubject $examSubject, array $records, ?Teacher $enteredBy = null, bool $replaceAll = true): ExamSubject
    {
        $examSubject->loadMissing('exam');

        if ($examSubject->exam->isPublished()) {
            throw ValidationException::withMessages([
                'records' => __('Marks cannot be edited after results are published.'),
            ]);
        }

        $eligibleIds = $examSubject->exam->eligibleStudents()->pluck('id')->map(fn ($id) => (int) $id)->all();
        $studentIds = collect($records)->pluck('student_id')->map(fn ($id) => (int) $id)->all();

        if ($studentIds !== array_values(array_unique($studentIds))) {
            throw ValidationException::withMessages([
                'records' => __('Duplicate student mark rows are not allowed.'),
            ]);
        }

        foreach ($studentIds as $studentId) {
            if (! in_array($studentId, $eligibleIds, true)) {
                throw ValidationException::withMessages([
                    'records' => __('All students must be eligible for this exam.'),
                ]);
            }
        }

        $max = (float) $examSubject->max_marks;
        $pass = (float) $examSubject->pass_mark;

        return DB::transaction(function () use ($examSubject, $records, $enteredBy, $max, $pass, $replaceAll): ExamSubject {
            $kept = [];

            foreach ($records as $index => $record) {
                $studentId = (int) $record['student_id'];
                $marksObtained = (float) $record['marks_obtained'];
                $kept[] = $studentId;

                try {
                    $result = $this->calculator->calculate($marksObtained, $max, $pass);
                } catch (\InvalidArgumentException $e) {
                    throw ValidationException::withMessages([
                        "records.{$index}.marks_obtained" => $e->getMessage(),
                    ]);
                }

                Mark::query()->updateOrCreate(
                    [
                        'exam_subject_id' => $examSubject->id,
                        'student_id' => $studentId,
                    ],
                    [
                        'marks_obtained' => $marksObtained,
                        'grade_letter' => $result['grade_letter'],
                        'is_pass' => $result['is_pass'],
                        'entered_by_teacher_id' => $enteredBy?->id,
                    ],
                );
            }

            if ($replaceAll) {
                Mark::query()
                    ->where('exam_subject_id', $examSubject->id)
                    ->whereNotIn('student_id', $kept)
                    ->delete();
            }

            return $examSubject->refresh()->load(['marks.student.user', 'subject', 'exam']);
        });
    }
}
