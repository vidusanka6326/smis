<?php

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

test('admin can manage teachers via policy', function () {
    $admin = User::factory()->admin()->create();
    $teacher = Teacher::factory()->create();

    expect(Gate::forUser($admin)->allows('viewAny', Teacher::class))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('create', Teacher::class))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('view', $teacher))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('update', $teacher))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('delete', $teacher))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('restore', $teacher))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('forceDelete', $teacher))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('manageAssignments', $teacher))->toBeTrue();
});

test('teacher can view and update own profile only', function () {
    $user = User::factory()->teacher()->create();
    $own = Teacher::factory()->create(['user_id' => $user->id]);
    $other = Teacher::factory()->create();

    expect(Gate::forUser($user)->allows('view', $own))->toBeTrue()
        ->and(Gate::forUser($user)->allows('update', $own))->toBeTrue()
        ->and(Gate::forUser($user)->denies('view', $other))->toBeTrue()
        ->and(Gate::forUser($user)->denies('update', $other))->toBeTrue()
        ->and(Gate::forUser($user)->denies('create', Teacher::class))->toBeTrue()
        ->and(Gate::forUser($user)->denies('delete', $own))->toBeTrue()
        ->and(Gate::forUser($user)->denies('manageAssignments', $own))->toBeTrue();
});

test('student cannot manage teachers', function () {
    $student = User::factory()->student()->create();
    $teacher = Teacher::factory()->create();

    expect(Gate::forUser($student)->denies('viewAny', Teacher::class))->toBeTrue()
        ->and(Gate::forUser($student)->denies('view', $teacher))->toBeTrue();
});
