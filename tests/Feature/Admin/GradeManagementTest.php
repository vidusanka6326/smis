<?php

use App\Models\Grade;
use App\Models\User;

test('admin can create update and delete grades', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('admin.grades.store'), [
            'number' => 5,
            'name' => 'Grade 5',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.grades.index'));

    $grade = Grade::query()->where('number', 5)->firstOrFail();

    $this->actingAs($admin)
        ->put(route('admin.grades.update', $grade), [
            'number' => 5,
            'name' => 'Grade Five',
        ])
        ->assertRedirect(route('admin.grades.index'));

    expect($grade->fresh()->name)->toBe('Grade Five');

    $this->actingAs($admin)
        ->delete(route('admin.grades.destroy', $grade))
        ->assertRedirect(route('admin.grades.index'));

    expect(Grade::query()->whereKey($grade->id)->exists())->toBeFalse();
});

test('grade numbers must be unique between 1 and 13', function () {
    $admin = User::factory()->admin()->create();
    Grade::factory()->number(3)->create();

    $this->actingAs($admin)
        ->post(route('admin.grades.store'), [
            'number' => 3,
            'name' => 'Duplicate',
        ])
        ->assertSessionHasErrors(['number']);

    $this->actingAs($admin)
        ->post(route('admin.grades.store'), [
            'number' => 14,
            'name' => 'Too high',
        ])
        ->assertSessionHasErrors(['number']);
});

test('student cannot manage grades', function () {
    $student = User::factory()->student()->create();

    $this->actingAs($student)
        ->get(route('admin.grades.index'))
        ->assertForbidden();
});
