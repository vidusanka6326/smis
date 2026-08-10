<?php

namespace Database\Factories;

use App\Enums\DayOfWeek;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TimetableEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TimetableEntry>
 */
class TimetableEntryFactory extends Factory
{
    protected $model = TimetableEntry::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'academic_year_id' => AcademicYear::factory(),
            'school_class_id' => SchoolClass::factory(),
            'day_of_week' => fake()->randomElement(DayOfWeek::cases()),
            'period_number' => fake()->numberBetween(1, TimetableEntry::MAX_PERIODS),
            'subject_id' => Subject::factory()->forGradeRange(1, 13),
            'teacher_id' => Teacher::factory(),
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (TimetableEntry $entry): void {
            $entry->loadMissing('schoolClass');

            if ($entry->schoolClass !== null) {
                $entry->schoolClass->subjects()->syncWithoutDetaching([$entry->subject_id]);
            }
        });
    }
}
