<?php

namespace Database\Factories;

use App\Enums\AttendanceStatus;
use App\Models\AttendanceSession;
use App\Models\Student;
use App\Models\StudentAttendance;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentAttendance>
 */
class StudentAttendanceFactory extends Factory
{
    protected $model = StudentAttendance::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'attendance_session_id' => AttendanceSession::factory(),
            'student_id' => Student::factory(),
            'status' => fake()->randomElement(AttendanceStatus::cases()),
        ];
    }
}
