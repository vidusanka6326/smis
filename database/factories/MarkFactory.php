<?php

namespace Database\Factories;

use App\Enums\GradeLetter;
use App\Models\ExamSubject;
use App\Models\Mark;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Mark>
 */
class MarkFactory extends Factory
{
    protected $model = Mark::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $marks = fake()->randomFloat(2, 0, 100);

        return [
            'exam_subject_id' => ExamSubject::factory(),
            'student_id' => Student::factory(),
            'marks_obtained' => $marks,
            'grade_letter' => GradeLetter::C,
            'is_pass' => $marks >= 40,
            'entered_by_teacher_id' => Teacher::factory(),
        ];
    }
}
