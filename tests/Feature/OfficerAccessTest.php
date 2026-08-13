<?php

use App\Models\User;

test('officer can access the school office dashboard and activity log', function () {
    $officer = User::factory()->officer()->create();

    $this->actingAs($officer)
        ->get(route('admin.dashboard'))
        ->assertOk();

    $this->actingAs($officer)
        ->get(route('admin.activity-logs.index'))
        ->assertOk()
        ->assertSee(__('Activity log'));
});

test('officer can open data-entry modules', function () {
    $officer = User::factory()->officer()->create();

    $this->actingAs($officer)
        ->get(route('admin.students.index'))
        ->assertOk();

    $this->actingAs($officer)
        ->get(route('admin.teachers.index'))
        ->assertOk();

    $this->actingAs($officer)
        ->get(route('admin.academic-years.index'))
        ->assertOk();

    $this->actingAs($officer)
        ->get(route('admin.attendance.sessions.index'))
        ->assertOk();

    $this->actingAs($officer)
        ->get(route('admin.exams.index'))
        ->assertOk();

    $this->actingAs($officer)
        ->get(route('admin.reports.dashboard'))
        ->assertOk();
});

test('officer is redirected to the admin dashboard after login routing', function () {
    $officer = User::factory()->officer()->create();

    expect($officer->dashboardRoute())->toBe('admin.dashboard')
        ->and($officer->isSchoolOffice())->toBeTrue()
        ->and($officer->isOfficer())->toBeTrue();
});

test('teacher still cannot access admin data-entry routes', function () {
    $teacher = User::factory()->teacher()->create();

    $this->actingAs($teacher)
        ->get(route('admin.students.index'))
        ->assertForbidden();
});
