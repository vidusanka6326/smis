<?php

use App\Models\User;

test('admin can open smis agent', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('agent.chat'))
        ->assertOk()
        ->assertSee('SMIS Agent');
});

test('officer can open smis agent', function () {
    $officer = User::factory()->officer()->create();

    $this->actingAs($officer)
        ->get(route('agent.chat'))
        ->assertOk();
});

test('teacher can open smis agent', function () {
    $teacher = User::factory()->teacher()->create();

    $this->actingAs($teacher)
        ->get(route('agent.chat'))
        ->assertOk();
});

test('student cannot open smis agent', function () {
    $student = User::factory()->student()->create();

    $this->actingAs($student)
        ->get(route('agent.chat'))
        ->assertForbidden();
});

test('guest is redirected from smis agent', function () {
    $this->get(route('agent.chat'))->assertRedirect();
});

test('admin dashboard sidebar includes smis agent', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('SMIS Agent');
});

test('student dashboard does not include smis agent', function () {
    $student = User::factory()->student()->create();

    $this->actingAs($student)
        ->get(route('student.dashboard'))
        ->assertOk()
        ->assertDontSee('SMIS Agent');
});
