<?php

use App\Enums\RoleName;
use App\Enums\UserStatus;
use App\Models\User;
use Laravel\Fortify\Features;

test('public registration is disabled', function () {
    expect(Features::enabled(Features::registration()))->toBeFalse();

    $this->get('/register')->assertNotFound();
    $this->post('/register', [])->assertNotFound();
});

test('admin can view the create user form', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.users.create'))
        ->assertOk()
        ->assertSee(__('Create user'));
});

test('admin can create a teacher account', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->post(route('admin.users.store'), [
        'name' => 'Jane Teacher',
        'email' => 'jane.teacher@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => RoleName::Teacher->value,
        'status' => UserStatus::Active->value,
    ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.dashboard'));

    $user = User::query()->where('email', 'jane.teacher@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->hasRole(RoleName::Teacher))->toBeTrue()
        ->and($user->status)->toBe(UserStatus::Active);
});

test('teacher cannot create users', function () {
    $teacher = User::factory()->teacher()->create();

    $this->actingAs($teacher)
        ->get(route('admin.users.create'))
        ->assertForbidden();

    $this->actingAs($teacher)
        ->post(route('admin.users.store'), [
            'name' => 'Blocked User',
            'email' => 'blocked@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => RoleName::Student->value,
            'status' => UserStatus::Active->value,
        ])
        ->assertForbidden();

    expect(User::query()->where('email', 'blocked@example.com')->exists())->toBeFalse();
});

test('student cannot create users', function () {
    $student = User::factory()->student()->create();

    $this->actingAs($student)
        ->post(route('admin.users.store'), [
            'name' => 'Blocked Student Create',
            'email' => 'blocked.student@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => RoleName::Student->value,
            'status' => UserStatus::Active->value,
        ])
        ->assertForbidden();
});

test('create user validates required fields', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('admin.users.store'), [])
        ->assertSessionHasErrors(['name', 'email', 'password', 'role', 'status']);
});
