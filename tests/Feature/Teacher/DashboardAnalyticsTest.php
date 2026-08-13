<?php

use App\Models\Teacher;
use App\Models\User;

test('teacher dashboard shows scoped analytics when profile exists', function () {
    $user = User::factory()->teacher()->create();
    Teacher::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user->fresh())
        ->get(route('teacher.dashboard'))
        ->assertOk()
        ->assertSee(__('Teacher Dashboard'))
        ->assertSee(__('Students in scope'))
        ->assertSee(__('Gender mix (my classes)'))
        ->assertSee(__('Subject pass rates'))
        ->assertSee(__('Attendance needing attention'))
        ->assertSee(__('Top performers'))
        ->assertSee('teacherSubjectPassChart');
});
