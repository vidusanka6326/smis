<?php

namespace Database\Factories;

use App\Models\Lesson;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lesson>
 */
class LessonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'teacher_id' => Teacher::factory(),
            'subject_id' => Subject::factory(),
            'title' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Lesson $lesson): void {
            if ($lesson->schoolClasses()->count() === 0) {
                $lesson->schoolClasses()->attach(SchoolClass::factory()->create());
            }
        });
    }
}
