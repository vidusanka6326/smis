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
        ->assertSee(__('Recent published results'));
});
