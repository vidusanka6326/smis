<?php

namespace Database\Factories;

use App\Enums\TeacherAssignmentRole;
use App\Models\SchoolClass;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TeacherAssignment>
 */
class TeacherAssignmentFactory extends Factory
{
    protected $model = TeacherAssignment::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $schoolClass = SchoolClass::factory()->create();

        return [
            'teacher_id' => Teacher::factory(),
            'school_class_id' => $schoolClass->id,
            'subject_id' => null,
            'academic_year_id' => $schoolClass->academic_year_id,
            'role_in_assignment' => TeacherAssignmentRole::ClassTeacher,
        ];
    }

    public function classTeacher(): static
    {
        return $this->state(fn (array $attributes): array => [
            'role_in_assignment' => TeacherAssignmentRole::ClassTeacher,
            'subject_id' => null,
        ]);
    }

    public function subjectTeacher(int $subjectId): static
    {
        return $this->state(fn (array $attributes): array => [
            'role_in_assignment' => TeacherAssignmentRole::SubjectTeacher,
            'subject_id' => $subjectId,
        ]);
    }
}
