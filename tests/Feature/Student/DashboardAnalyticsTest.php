<?php

use App\Models\Student;
use App\Models\User;

test('student dashboard shows attendance and results widgets', function () {
    $user = User::factory()->student()->create();
    Student::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user->fresh())
        ->get(route('student.dashboard'))
        ->assertOk()
        ->assertSee(__('This month'))
        ->assertSee(__('Exam average'))
        ->assertSee(__('Today'))
        ->assertSee(__('Latest results'))
        ->assertSee(__('Subject averages'))
        ->assertSee('studentSubjectsChart');
});
