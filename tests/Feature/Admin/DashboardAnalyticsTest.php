<?php

use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;

test('admin dashboard shows analytics widgets', function () {
    $admin = User::factory()->admin()->create();
    Student::factory()->count(2)->create();
    Teacher::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee(__('School at a glance'))
        ->assertSee(__('Attendance by class'))
        ->assertSee(__('Needs attention'))
        ->assertSee(__('Recent activity'))
        ->assertSee('adminAttendanceChart');
});
