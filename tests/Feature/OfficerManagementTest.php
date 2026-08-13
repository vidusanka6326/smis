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

test('admin can view the officers index and create form', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.officers.index'))
        ->assertOk()
        ->assertSee(__('Officers'));

    $this->actingAs($admin)
        ->get(route('admin.officers.create'))
        ->assertOk()
        ->assertSee(__('Add officer'));
});

test('admin can create an officer account', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->post(route('admin.officers.store'), [
        'name' => 'Office Clerk',
        'email' => 'office.clerk@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'status' => UserStatus::Active->value,
    ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.officers.index'));

    $user = User::query()->where('email', 'office.clerk@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->hasRole(RoleName::Officer))->toBeTrue()
        ->and($user->status)->toBe(UserStatus::Active);
});

test('admin can update and soft-delete an officer', function () {
    $admin = User::factory()->admin()->create();
    $officer = User::factory()->officer()->create([
        'email' => 'old.officer@example.com',
    ]);

    $this->actingAs($admin)
        ->put(route('admin.officers.update', $officer), [
            'name' => 'Updated Officer',
            'email' => 'updated.officer@example.com',
            'status' => UserStatus::Inactive->value,
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.officers.index'));

    expect($officer->refresh()->name)->toBe('Updated Officer')
        ->and($officer->email)->toBe('updated.officer@example.com')
        ->and($officer->status)->toBe(UserStatus::Inactive);

    $this->actingAs($admin)
        ->delete(route('admin.officers.destroy', $officer))
        ->assertRedirect(route('admin.officers.index'));

    expect(User::query()->where('email', 'updated.officer@example.com')->exists())->toBeFalse()
        ->and(User::withTrashed()->where('email', 'updated.officer@example.com')->exists())->toBeTrue();
});

test('officer cannot manage the officers section', function () {
    $officer = User::factory()->officer()->create();

    $this->actingAs($officer)
        ->get(route('admin.officers.index'))
        ->assertForbidden();

    $this->actingAs($officer)
        ->post(route('admin.officers.store'), [
            'name' => 'Blocked',
            'email' => 'blocked.officer@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'status' => UserStatus::Active->value,
        ])
        ->assertForbidden();
});

test('teacher cannot manage officers', function () {
    $teacher = User::factory()->teacher()->create();

    $this->actingAs($teacher)
        ->get(route('admin.officers.create'))
        ->assertForbidden();
});

test('create officer validates required fields', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('admin.officers.store'), [])
        ->assertSessionHasErrors(['name', 'email', 'password', 'status']);
});
