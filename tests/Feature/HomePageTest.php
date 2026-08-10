<?php

test('homepage presents smis branding and sign-in call to action', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee(config('app.name', 'SMIS'), false)
        ->assertSee(__('School data, gathered and managed in one place.'), false)
        ->assertSee(__('Sign in to SMIS'), false)
        ->assertSee(route('login'), false);
});

test('authenticated users see dashboard call to action on homepage', function () {
    $user = \App\Models\User::factory()->admin()->create();

    $this->actingAs($user)
        ->get(route('home'))
        ->assertOk()
        ->assertSee(__('Continue to dashboard'), false)
        ->assertDontSee(__('Sign in to SMIS'), false);
});
