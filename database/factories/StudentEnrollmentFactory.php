<?php

namespace Database\Factories;

use App\Enums\EnrollmentStatus;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentEnrollment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentEnrollment>
 */
class StudentEnrollmentFactory extends Factory
{
    protected $model = StudentEnrollment::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $schoolClass = SchoolClass::factory()->create();

        return [
            'student_id' => Student::factory(),
            'school_class_id' => $schoolClass->id,
            'academic_year_id' => $schoolClass->academic_year_id,
            'status' => EnrollmentStatus::Active,
        ];
    }
}
