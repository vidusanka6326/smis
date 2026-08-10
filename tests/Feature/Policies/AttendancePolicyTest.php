<?php

use App\Enums\AttendanceStatus;
use App\Enums\TeacherAssignmentRole;
use App\Models\AcademicYear;
use App\Models\AttendanceSession;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use App\Models\TeacherAttendance;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

test('admin can manage attendance sessions and teacher attendance', function () {
    $admin = User::factory()->admin()->create();
    $session = AttendanceSession::factory()->create();
    $teacherAttendance = TeacherAttendance::factory()->create();

    expect(Gate::forUser($admin)->allows('viewAny', AttendanceSession::class))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('create', AttendanceSession::class))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('update', $session))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('delete', $session))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('create', TeacherAttendance::class))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('delete', $teacherAttendance))->toBeTrue();
});

test('class teacher can update own class session but not delete', function () {
    $user = User::factory()->teacher()->create();
    $teacher = Teacher::factory()->create(['user_id' => $user->id]);
    $year = AcademicYear::factory()->create();
    $grade = Grade::factory()->number(3)->create();
    $schoolClass = SchoolClass::factory()->create([
        'academic_year_id' => $year->id,
        'grade_id' => $grade->id,
        'class_teacher_id' => $teacher->id,
    ]);
    TeacherAssignment::factory()->create([
        'teacher_id' => $teacher->id,
        'school_class_id' => $schoolClass->id,
        'academic_year_id' => $year->id,
        'role_in_assignment' => TeacherAssignmentRole::ClassTeacher,
        'subject_id' => null,
    ]);
    $session = AttendanceSession::factory()->forClass($schoolClass)->create();

    expect(Gate::forUser($user)->allows('update', $session))->toBeTrue()
        ->and(Gate::forUser($user)->denies('delete', $session))->toBeTrue()
        ->and(Gate::forUser($user)->allows('createForClass', [AttendanceSession::class, $schoolClass, null]))->toBeTrue();
});

test('teacher cannot update finalized session', function () {
    $user = User::factory()->teacher()->create();
    $teacher = Teacher::factory()->create(['user_id' => $user->id]);
    $year = AcademicYear::factory()->create();
    $schoolClass = SchoolClass::factory()->create([
        'academic_year_id' => $year->id,
        'class_teacher_id' => $teacher->id,
    ]);
    $session = AttendanceSession::factory()->forClass($schoolClass)->finalized()->create();

    expect(Gate::forUser($user)->denies('update', $session))->toBeTrue();
});

test('student can view own attendance record only', function () {
    $user = User::factory()->student()->create();
    $student = Student::factory()->create(['user_id' => $user->id]);
    $own = StudentAttendance::factory()->create([
        'student_id' => $student->id,
        'status' => AttendanceStatus::Present,
    ]);
    $other = StudentAttendance::factory()->create();

    expect(Gate::forUser($user)->allows('view', $own))->toBeTrue()
        ->and(Gate::forUser($user)->denies('view', $other))->toBeTrue()
        ->and(Gate::forUser($user)->denies('create', AttendanceSession::class))->toBeTrue();
});

test('teacher can view and update own teacher attendance only', function () {
    $user = User::factory()->teacher()->create();
    $teacher = Teacher::factory()->create(['user_id' => $user->id]);
    $own = TeacherAttendance::factory()->create(['teacher_id' => $teacher->id]);
    $other = TeacherAttendance::factory()->create();

    expect(Gate::forUser($user)->allows('view', $own))->toBeTrue()
        ->and(Gate::forUser($user)->allows('update', $own))->toBeTrue()
        ->and(Gate::forUser($user)->denies('view', $other))->toBeTrue()
        ->and(Gate::forUser($user)->denies('update', $other))->toBeTrue();
});
