<?php

use App\Enums\AttendanceStatus;
use App\Models\Teacher;
use App\Models\TeacherAttendance;
use App\Models\User;

test('admin can record teacher attendance', function () {
    $admin = User::factory()->admin()->create();
    $teacher = Teacher::factory()->create();

    $this->actingAs($admin)
        ->post(route('admin.attendance.teachers.store'), [
            'teacher_id' => $teacher->id,
            'date' => now()->toDateString(),
            'status' => AttendanceStatus::Present->value,
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    expect(TeacherAttendance::query()->count())->toBe(1);
});

test('admin cannot double-book teacher attendance on same date', function () {
    $admin = User::factory()->admin()->create();
    $teacher = Teacher::factory()->create();

    TeacherAttendance::factory()->create([
        'teacher_id' => $teacher->id,
        'date' => now()->toDateString(),
    ]);

    $this->actingAs($admin)
        ->post(route('admin.attendance.teachers.store'), [
            'teacher_id' => $teacher->id,
            'date' => now()->toDateString(),
            'status' => AttendanceStatus::Absent->value,
        ])
        ->assertSessionHasErrors(['date']);
});

test('student cannot manage teacher attendance', function () {
    $student = User::factory()->student()->create();

    $this->actingAs($student)
        ->get(route('admin.attendance.teachers.index'))
        ->assertForbidden();
});
