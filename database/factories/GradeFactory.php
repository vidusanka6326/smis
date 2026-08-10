<?php

namespace Database\Factories;

use App\Models\Grade;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Grade>
 */
class GradeFactory extends Factory
{
    protected $model = Grade::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Keep random numbers outside 1–13 so explicit ->number() states used in tests never collide.
        $number = fake()->unique()->numberBetween(50, 9999);

        return [
            'number' => $number,
            'name' => sprintf('Grade %d', $number),
        ];
    }

    public function number(int $number): static
    {
        return $this->state(fn (array $attributes): array => [
            'number' => $number,
            'name' => sprintf('Grade %d', $number),
        ]);
    }
}
