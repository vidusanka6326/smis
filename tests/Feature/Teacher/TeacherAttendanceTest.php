<?php

use App\Enums\AttendanceStatus;
use App\Models\Teacher;
use App\Models\TeacherAttendance;
use App\Models\User;

test('teacher can record own attendance', function () {
    $user = User::factory()->teacher()->create();
    $teacher = Teacher::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->post(route('teacher.attendance.self.store'), [
            'teacher_id' => $teacher->id,
            'date' => now()->toDateString(),
            'status' => AttendanceStatus::Present->value,
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    expect(TeacherAttendance::query()->where('teacher_id', $teacher->id)->count())->toBe(1);
});

test('teacher cannot record another teacher attendance via self route', function () {
    $user = User::factory()->teacher()->create();
    Teacher::factory()->create(['user_id' => $user->id]);
    $other = Teacher::factory()->create();

    $this->actingAs($user)
        ->post(route('teacher.attendance.self.store'), [
            'teacher_id' => $other->id,
            'date' => now()->toDateString(),
            'status' => AttendanceStatus::Present->value,
        ])
        ->assertForbidden();
});
