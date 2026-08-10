<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AcademicYear>
 */
class AcademicYearFactory extends Factory
{
    protected $model = AcademicYear::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startYear = fake()->numberBetween(2000, 2090);

        return [
            'name' => fake()->unique()->bothify($startYear.'/'.($startYear + 1).'-###'),
            'starts_on' => sprintf('%d-01-01', $startYear),
            'ends_on' => sprintf('%d-12-31', $startYear),
            'is_current' => false,
        ];
    }

    public function current(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_current' => true,
        ]);
    }
}
