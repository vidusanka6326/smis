<?php

namespace App\Actions\Examination;

use App\Models\Exam;
use App\Models\ExamSubject;
use App\Models\Subject;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SyncExamSubjects
{
    /**
     * @param  list<array{subject_id: int, max_marks: float|int|string, pass_mark: float|int|string}>  $subjects
     */
    public function handle(Exam $exam, array $subjects): Exam
    {
        if ($exam->isPublished()) {
            throw ValidationException::withMessages([
                'subjects' => __('Cannot change subjects on a published exam.'),
            ]);
        }

        $subjectIds = collect($subjects)->pluck('subject_id')->map(fn ($id) => (int) $id)->all();

        if ($subjectIds !== array_values(array_unique($subjectIds))) {
            throw ValidationException::withMessages([
                'subjects' => __('Duplicate subjects are not allowed.'),
            ]);
        }

        foreach ($subjects as $index => $row) {
            $max = (float) $row['max_marks'];
            $pass = (float) $row['pass_mark'];

            if ($max <= 0) {
                throw ValidationException::withMessages([
                    "subjects.{$index}.max_marks" => __('Max marks must be greater than zero.'),
                ]);
            }

            if ($pass < 0 || $pass > $max) {
                throw ValidationException::withMessages([
                    "subjects.{$index}.pass_mark" => __('Pass mark must be between 0 and max marks.'),
                ]);
            }

            Subject::query()->findOrFail((int) $row['subject_id']);
        }

        return DB::transaction(function () use ($exam, $subjects, $subjectIds): Exam {
            $kept = [];

            foreach ($subjects as $row) {
                $subjectId = (int) $row['subject_id'];
                $kept[] = $subjectId;

                ExamSubject::query()->updateOrCreate(
                    [
                        'exam_id' => $exam->id,
                        'subject_id' => $subjectId,
                    ],
                    [
                        'max_marks' => $row['max_marks'],
                        'pass_mark' => $row['pass_mark'],
                    ],
                );
            }

            ExamSubject::query()
                ->where('exam_id', $exam->id)
                ->whereNotIn('subject_id', $kept)
                ->whereDoesntHave('marks')
                ->delete();

            $orphans = ExamSubject::query()
                ->where('exam_id', $exam->id)
                ->whereNotIn('subject_id', $subjectIds)
                ->whereHas('marks')
                ->exists();

            if ($orphans) {
                throw ValidationException::withMessages([
                    'subjects' => __('Cannot remove subjects that already have marks entered.'),
                ]);
            }

            return $exam->refresh()->load(['examSubjects.subject']);
        });
    }
}
