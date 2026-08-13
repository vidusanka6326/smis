<?php

use App\Models\Teacher;
use App\Models\User;

test('teacher dashboard shows scoped analytics when profile exists', function () {
    $user = User::factory()->teacher()->create();
    Teacher::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user->fresh())
        ->get(route('teacher.dashboard'))
        ->assertOk()
        ->assertSee(__('Here’s your class pulse'))
        ->assertSee(__('Month attendance'))
        ->assertSee(__('Who needs you'))
        ->assertSee(__('Standing out'))
        ->assertSee('teacherAttendanceChart');
});
