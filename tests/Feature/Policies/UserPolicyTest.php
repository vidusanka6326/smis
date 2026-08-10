<?php

use App\Models\User;
use Illuminate\Support\Facades\Gate;

test('admin can create and manage users', function () {
    $admin = User::factory()->admin()->create();
    $other = User::factory()->create();

    expect(Gate::forUser($admin)->allows('viewAny', User::class))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('create', User::class))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('view', $other))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('update', $other))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('delete', $other))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('restore', $other))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('forceDelete', $other))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('assignRole', User::class))->toBeTrue();
});

test('teacher cannot manage other users', function () {
    $teacher = User::factory()->teacher()->create();
    $other = User::factory()->create();

    expect(Gate::forUser($teacher)->denies('viewAny', User::class))->toBeTrue()
        ->and(Gate::forUser($teacher)->denies('create', User::class))->toBeTrue()
        ->and(Gate::forUser($teacher)->denies('view', $other))->toBeTrue()
        ->and(Gate::forUser($teacher)->denies('update', $other))->toBeTrue()
        ->and(Gate::forUser($teacher)->denies('delete', $other))->toBeTrue()
        ->and(Gate::forUser($teacher)->denies('restore', $other))->toBeTrue()
        ->and(Gate::forUser($teacher)->denies('forceDelete', $other))->toBeTrue()
        ->and(Gate::forUser($teacher)->denies('assignRole', User::class))->toBeTrue();
});

test('student cannot manage users but can view self', function () {
    $student = User::factory()->student()->create();
    $other = User::factory()->create();

    expect(Gate::forUser($student)->allows('view', $student))->toBeTrue()
        ->and(Gate::forUser($student)->allows('update', $student))->toBeTrue()
        ->and(Gate::forUser($student)->denies('delete', $student))->toBeTrue()
        ->and(Gate::forUser($student)->denies('view', $other))->toBeTrue()
        ->and(Gate::forUser($student)->denies('create', User::class))->toBeTrue()
        ->and(Gate::forUser($student)->denies('assignRole', User::class))->toBeTrue();
});

test('admin cannot delete themselves', function () {
    $admin = User::factory()->admin()->create();

    expect(Gate::forUser($admin)->denies('delete', $admin))->toBeTrue()
        ->and(Gate::forUser($admin)->denies('forceDelete', $admin))->toBeTrue();
});
