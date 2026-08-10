<?php

namespace Database\Factories;

use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subject>
 */
class SubjectFactory extends Factory
{
    protected $model = Subject::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'code' => strtoupper(fake()->unique()->bothify('SUB###')),
            'min_grade' => 1,
            'max_grade' => 13,
        ];
    }

    public function forGradeRange(int $min, int $max): static
    {
        return $this->state(fn (array $attributes): array => [
            'min_grade' => $min,
            'max_grade' => $max,
        ]);
    }
}
