<?php

use App\Enums\AppLocale;
use App\Models\User;

test('guests can switch the interface to sinhala', function () {
    $this->from(route('home'))
        ->post(route('locale.update'), ['locale' => AppLocale::Sinhala->value])
        ->assertRedirect(route('home'));

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('lang="si"', false)
        ->assertSee('පාසල් දත්ත, එක තැනකින් රැස් කර කළමනාකරණය කරයි.', false)
        ->assertDontSee('School data, gathered and managed in one place.', false);
});

test('guests can switch the interface to tamil', function () {
    $this->from(route('home'))
        ->post(route('locale.update'), ['locale' => AppLocale::Tamil->value])
        ->assertRedirect(route('home'));

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('lang="ta"', false)
        ->assertSee('பள்ளித் தரவு, ஒரே இடத்தில் திரட்டி நிர்வகிக்கப்படும்.', false);
});

test('unsupported locales are rejected', function () {
    $this->from(route('home'))
        ->post(route('locale.update'), ['locale' => 'fr'])
        ->assertRedirect(route('home'))
        ->assertSessionHasErrors('locale');

    $this->get(route('home'))
        ->assertSee('lang="en"', false)
        ->assertSee('School data, gathered and managed in one place.', false);
});

test('authenticated users persist locale on their account', function () {
    $user = User::factory()->admin()->create(['locale' => AppLocale::English->value]);

    $this->actingAs($user)
        ->from(route('admin.dashboard'))
        ->post(route('locale.update'), ['locale' => AppLocale::Sinhala->value])
        ->assertRedirect(route('admin.dashboard'));

    expect($user->fresh()->locale)->toBe(AppLocale::Sinhala->value);

    $this->get(route('appearance.edit'))
        ->assertOk()
        ->assertSee('lang="si"', false)
        ->assertSee('භාෂාව', false);
});

test('saved user locale is applied on a new session', function () {
    $user = User::factory()->admin()->create(['locale' => AppLocale::Tamil->value]);

    $this->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('lang="ta"', false);
});

test('dashboard shows a split eng sin tam language selector', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('data-test="language-switcher"', false)
        ->assertSee('>ENG<', false)
        ->assertSee('>SIN<', false)
        ->assertSee('>TAM<', false);
});

test('language switcher is shown on the homepage and login', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('සිංහල', false)
        ->assertSee('தமிழ்', false);

    $this->get(route('login'))
        ->assertOk()
        ->assertSee('data-test="language-switcher"', false)
        ->assertSee('>ENG<', false)
        ->assertSee('>SIN<', false)
        ->assertSee('>TAM<', false);
});
