<?php

namespace Database\Factories;

use App\Enums\RoleName;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Teacher>
 */
class TeacherFactory extends Factory
{
    protected $model = Teacher::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->teacher(),
            'employee_no' => strtoupper(fake()->unique()->bothify('TCH-####')),
            'phone' => fake()->optional()->numerify('07########'),
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Teacher $teacher): void {
            if (! $teacher->user->hasRole(RoleName::Teacher)) {
                $teacher->user->assignRole(RoleName::Teacher);
            }
        });
    }
}
