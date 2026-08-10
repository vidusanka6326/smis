<?php

namespace Database\Factories;

use App\Enums\Gender;
use App\Enums\RoleName;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Student>
 */
class StudentFactory extends Factory
{
    protected $model = Student::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->student(),
            'admission_no' => strtoupper(fake()->unique()->bothify('ADM-######')),
            'date_of_birth' => fake()->dateTimeBetween('-18 years', '-5 years')->format('Y-m-d'),
            'gender' => fake()->randomElement(Gender::cases()),
            'guardian_name' => fake()->name(),
            'guardian_phone' => fake()->numerify('07########'),
            'guardian_email' => fake()->optional()->safeEmail(),
            'guardian_relationship' => fake()->randomElement(['Mother', 'Father', 'Guardian']),
            'current_class_id' => null,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Student $student): void {
            if (! $student->user->hasRole(RoleName::Student)) {
                $student->user->assignRole(RoleName::Student);
            }
        });
    }
}
