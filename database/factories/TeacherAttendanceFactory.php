<?php

namespace Database\Factories;

use App\Enums\AttendanceStatus;
use App\Models\Teacher;
use App\Models\TeacherAttendance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TeacherAttendance>
 */
class TeacherAttendanceFactory extends Factory
{
    protected $model = TeacherAttendance::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'teacher_id' => Teacher::factory(),
            'date' => fake()->unique()->date(),
            'status' => fake()->randomElement(AttendanceStatus::cases()),
            'recorded_by' => User::factory()->admin(),
            'notes' => null,
        ];
    }
}
