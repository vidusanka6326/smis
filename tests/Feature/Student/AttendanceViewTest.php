<?php

use App\Enums\AttendanceStatus;
use App\Models\AcademicYear;
use App\Models\AttendanceSession;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\User;

test('student can view own attendance summary', function () {
    $user = User::factory()->student()->create();
    $year = AcademicYear::factory()->current()->create();
    $grade = Grade::factory()->number(4)->create();
    $schoolClass = SchoolClass::factory()->create([
        'academic_year_id' => $year->id,
        'grade_id' => $grade->id,
    ]);
    $student = Student::factory()->create([
        'user_id' => $user->id,
        'current_class_id' => $schoolClass->id,
    ]);

    $session = AttendanceSession::factory()->forClass($schoolClass)->create([
        'date' => now()->toDateString(),
    ]);
    StudentAttendance::factory()->create([
        'attendance_session_id' => $session->id,
        'student_id' => $student->id,
        'status' => AttendanceStatus::Present,
    ]);

    $this->actingAs($user)
        ->get(route('student.attendance', ['month' => now()->format('Y-m')]))
        ->assertOk()
        ->assertSee('100%')
        ->assertSee(__('Present'));
});

test('teacher cannot open student attendance page', function () {
    $teacher = User::factory()->teacher()->create();

    $this->actingAs($teacher)
        ->get(route('student.attendance'))
        ->assertForbidden();
});
