<?php

use App\Enums\UserStatus;
use App\Models\User;

test('inactive users cannot authenticate', function () {
    $user = User::factory()->inactive()->create();

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('active middleware logs out inactive users', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user);

    $user->update(['status' => UserStatus::Inactive]);

    $this->get(route('admin.dashboard'))
        ->assertRedirect(route('login'));

    $this->assertGuest();
});
