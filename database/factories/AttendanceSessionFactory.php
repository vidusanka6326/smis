<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\AttendanceSession;
use App\Models\SchoolClass;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AttendanceSession>
 */
class AttendanceSessionFactory extends Factory
{
    protected $model = AttendanceSession::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'academic_year_id' => AcademicYear::factory(),
            'school_class_id' => SchoolClass::factory(),
            'subject_id' => null,
            'date' => fake()->date(),
            'scope' => AttendanceSession::SCOPE_CLASS,
            'taken_by_teacher_id' => Teacher::factory(),
            'finalized_at' => null,
            'notes' => null,
        ];
    }

    public function forClass(SchoolClass $schoolClass): static
    {
        return $this->state(fn (): array => [
            'academic_year_id' => $schoolClass->academic_year_id,
            'school_class_id' => $schoolClass->id,
            'subject_id' => null,
            'scope' => AttendanceSession::SCOPE_CLASS,
        ]);
    }

    public function finalized(): static
    {
        return $this->state(fn (): array => [
            'finalized_at' => now(),
        ]);
    }
}
