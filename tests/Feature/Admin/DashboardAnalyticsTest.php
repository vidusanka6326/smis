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
        ->assertSee(__('Admin Dashboard'))
        ->assertSee(__('Students'))
        ->assertSee(__('Gender mix'))
        ->assertSee(__('Students by grade'))
        ->assertSee(__('Students by class'))
        ->assertSee(__('Pass vs fail (latest exam)'))
        ->assertSee(__('Attendance needing attention'))
        ->assertSee(__('Recent activity'))
        ->assertSee('adminGenderChart')
        ->assertSee('adminSubjectPassChart');
});
