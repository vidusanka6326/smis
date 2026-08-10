<?php

namespace Database\Factories;

use App\Enums\ExamType;
use App\Models\AcademicYear;
use App\Models\Exam;
use App\Models\Grade;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Exam>
 */
class ExamFactory extends Factory
{
    protected $model = Exam::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $starts = fake()->dateTimeBetween('-1 month', '+1 month');

        return [
            'name' => fake()->unique()->sentence(3),
            'type' => fake()->randomElement(ExamType::cases()),
            'academic_year_id' => AcademicYear::factory(),
            'grade_id' => Grade::factory(),
            'school_class_id' => null,
            'starts_on' => $starts->format('Y-m-d'),
            'ends_on' => (clone $starts)->modify('+7 days')->format('Y-m-d'),
            'published_at' => null,
            'created_by' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (): array => [
            'published_at' => now(),
        ]);
    }
}
