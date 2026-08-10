<?php

use App\Models\User;

test('admin is redirected from dashboard hub to admin dashboard', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertRedirect(route('admin.dashboard'));
});

test('teacher is redirected from dashboard hub to teacher dashboard', function () {
    $teacher = User::factory()->teacher()->create();

    $this->actingAs($teacher)
        ->get(route('dashboard'))
        ->assertRedirect(route('teacher.dashboard'));
});

test('student is redirected from dashboard hub to student dashboard', function () {
    $student = User::factory()->student()->create();

    $this->actingAs($student)
        ->get(route('dashboard'))
        ->assertRedirect(route('student.dashboard'));
});

test('admin can open admin dashboard and not teacher dashboard', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee(__('Admin Dashboard'));

    $this->actingAs($admin)
        ->get(route('teacher.dashboard'))
        ->assertForbidden();
});

test('teacher cannot open admin dashboard', function () {
    $teacher = User::factory()->teacher()->create();

    $this->actingAs($teacher)
        ->get(route('admin.dashboard'))
        ->assertForbidden();

    $this->actingAs($teacher)
        ->get(route('teacher.dashboard'))
        ->assertOk()
        ->assertSee(__('Teacher Dashboard'));
});

test('student cannot open admin or teacher dashboards', function () {
    $student = User::factory()->student()->create();

    $this->actingAs($student)
        ->get(route('admin.dashboard'))
        ->assertForbidden();

    $this->actingAs($student)
        ->get(route('teacher.dashboard'))
        ->assertForbidden();

    $this->actingAs($student)
        ->get(route('student.dashboard'))
        ->assertOk()
        ->assertSee(__('Student Dashboard'));
});

test('admin login lands on admin dashboard', function () {
    $admin = User::factory()->admin()->create();

    $this->post(route('login.store'), [
        'email' => $admin->email,
        'password' => 'password',
    ])->assertRedirect(route('admin.dashboard', absolute: false));

    $this->assertAuthenticatedAs($admin);
});
