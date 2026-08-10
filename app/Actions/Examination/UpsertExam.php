<?php

namespace App\Actions\Examination;

use App\Enums\ExamType;
use App\Models\Exam;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpsertExam
{
    /**
     * @param  array{
     *     name: string,
     *     type: string,
     *     academic_year_id: int,
     *     grade_id?: int|null,
     *     school_class_id?: int|null,
     *     starts_on: string,
     *     ends_on: string
     * }  $data
     */
    public function handle(array $data, User $actor, ?Exam $existing = null): Exam
    {
        if ($existing?->isPublished()) {
            throw ValidationException::withMessages([
                'name' => __('Published exams cannot be edited. Unpublish first if changes are required.'),
            ]);
        }

        $gradeId = isset($data['grade_id']) && $data['grade_id'] !== '' && $data['grade_id'] !== null
            ? (int) $data['grade_id']
            : null;
        $schoolClassId = isset($data['school_class_id']) && $data['school_class_id'] !== '' && $data['school_class_id'] !== null
            ? (int) $data['school_class_id']
            : null;

        if ($gradeId === null && $schoolClassId === null) {
            throw ValidationException::withMessages([
                'grade_id' => __('Select a grade and/or a class for the exam scope.'),
            ]);
        }

        if ($schoolClassId !== null) {
            $schoolClass = SchoolClass::query()->findOrFail($schoolClassId);

            if ((int) $schoolClass->academic_year_id !== (int) $data['academic_year_id']) {
                throw ValidationException::withMessages([
                    'school_class_id' => __('The class must belong to the selected academic year.'),
                ]);
            }

            if ($gradeId !== null && (int) $schoolClass->grade_id !== $gradeId) {
                throw ValidationException::withMessages([
                    'grade_id' => __('The grade must match the selected class.'),
                ]);
            }

            $gradeId ??= (int) $schoolClass->grade_id;
        }

        if ($gradeId !== null) {
            Grade::query()->findOrFail($gradeId);
        }

        if ($data['ends_on'] < $data['starts_on']) {
            throw ValidationException::withMessages([
                'ends_on' => __('End date must be on or after the start date.'),
            ]);
        }

        return DB::transaction(function () use ($data, $actor, $existing, $gradeId, $schoolClassId): Exam {
            $payload = [
                'name' => $data['name'],
                'type' => ExamType::from($data['type']),
                'academic_year_id' => $data['academic_year_id'],
                'grade_id' => $gradeId,
                'school_class_id' => $schoolClassId,
                'starts_on' => $data['starts_on'],
                'ends_on' => $data['ends_on'],
                'created_by' => $existing?->created_by ?? $actor->id,
            ];

            if ($existing !== null) {
                $existing->update($payload);

                return $existing->refresh()->load(['academicYear', 'grade', 'schoolClass']);
            }

            return Exam::query()->create($payload)->load(['academicYear', 'grade', 'schoolClass']);
        });
    }
}
