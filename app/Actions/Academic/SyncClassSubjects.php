<?php

namespace App\Actions\Academic;

use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SyncClassSubjects
{
    /**
     * Attach subjects to a class, ensuring each subject applies to the class grade.
     *
     * @param  list<int|string>  $subjectIds
     */
    public function handle(SchoolClass $schoolClass, array $subjectIds): SchoolClass
    {
        $schoolClass->loadMissing('grade');

        $ids = collect($subjectIds)
            ->filter(fn ($id): bool => $id !== null && $id !== '')
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            $schoolClass->subjects()->sync([]);

            return $schoolClass->refresh();
        }

        $subjects = Subject::query()->whereIn('id', $ids)->get();

        if ($subjects->count() !== $ids->count()) {
            throw ValidationException::withMessages([
                'subject_ids' => __('One or more selected subjects are invalid.'),
            ]);
        }

        $invalid = $subjects->reject(
            fn (Subject $subject): bool => $subject->appliesToGrade($schoolClass->grade->number),
        );

        if ($invalid->isNotEmpty()) {
            throw ValidationException::withMessages([
                'subject_ids' => __('Each subject must apply to grade :grade.', [
                    'grade' => $schoolClass->grade->number,
                ]),
            ]);
        }

        return DB::transaction(function () use ($schoolClass, $ids): SchoolClass {
            $schoolClass->subjects()->sync($ids->all());

            return $schoolClass->refresh();
        });
    }
}
