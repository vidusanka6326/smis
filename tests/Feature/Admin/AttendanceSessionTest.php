<?php

use App\Enums\AttendanceStatus;
use App\Models\AcademicYear;
use App\Models\AttendanceSession;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\User;

test('admin can create a class attendance session', function () {
    $admin = User::factory()->admin()->create();
    [$year, $schoolClass, $student] = attendanceFixtures();

    $this->actingAs($admin)
        ->post(route('admin.attendance.sessions.store'), [
            'academic_year_id' => $year->id,
            'school_class_id' => $schoolClass->id,
            'date' => now()->toDateString(),
            'records' => [
                [
                    'student_id' => $student->id,
                    'status' => AttendanceStatus::Present->value,
                ],
            ],
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    expect(AttendanceSession::query()->count())->toBe(1)
        ->and(StudentAttendance::query()->count())->toBe(1);
});

test('admin cannot create duplicate class session for same date', function () {
    $admin = User::factory()->admin()->create();
    [$year, $schoolClass, $student] = attendanceFixtures();

    AttendanceSession::factory()->forClass($schoolClass)->create([
        'date' => now()->toDateString(),
    ]);

    $this->actingAs($admin)
        ->post(route('admin.attendance.sessions.store'), [
            'academic_year_id' => $year->id,
            'school_class_id' => $schoolClass->id,
            'date' => now()->toDateString(),
            'records' => [
                [
                    'student_id' => $student->id,
                    'status' => AttendanceStatus::Absent->value,
                ],
            ],
        ])
        ->assertSessionHasErrors(['date']);
});

test('teacher cannot access admin attendance routes', function () {
    $teacher = User::factory()->teacher()->create();

    $this->actingAs($teacher)
        ->get(route('admin.attendance.sessions.index'))
        ->assertForbidden();
});

test('admin can view monthly attendance summary', function () {
    $admin = User::factory()->admin()->create();
    [$year, $schoolClass, $student] = attendanceFixtures();

    $session = AttendanceSession::factory()->forClass($schoolClass)->create([
        'date' => now()->toDateString(),
    ]);
    StudentAttendance::factory()->create([
        'attendance_session_id' => $session->id,
        'student_id' => $student->id,
        'status' => AttendanceStatus::Present,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.attendance.monthly', [
            'month' => now()->format('Y-m'),
            'school_class_id' => $schoolClass->id,
        ]))
        ->assertOk()
        ->assertSee($student->user->name)
        ->assertSee('100%');
});

/**
 * @return array{0: AcademicYear, 1: SchoolClass, 2: Student}
 */
function attendanceFixtures(): array
{
    $year = AcademicYear::factory()->current()->create();
    $grade = Grade::factory()->number(8)->create();
    $schoolClass = SchoolClass::factory()->create([
        'academic_year_id' => $year->id,
        'grade_id' => $grade->id,
    ]);
    $student = Student::factory()->create([
        'current_class_id' => $schoolClass->id,
    ]);

    return [$year, $schoolClass, $student];
}
