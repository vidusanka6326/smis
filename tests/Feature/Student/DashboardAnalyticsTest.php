<?php

use App\Models\Student;
use App\Models\User;

test('student dashboard shows attendance and results widgets', function () {
    $user = User::factory()->student()->create();
    Student::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user->fresh())
        ->get(route('student.dashboard'))
        ->assertOk()
        ->assertSee(__('Student Dashboard'))
        ->assertSee(__('Attendance (month)'))
        ->assertSee(__('Overall exam avg'))
        ->assertSee(__('Pass vs fail'))
        ->assertSee(__('Subject averages %'))
        ->assertSee(__('Subjects to improve'))
        ->assertSee(__('Recent published results'))
        ->assertSee('studentSubjectsChart');
});
