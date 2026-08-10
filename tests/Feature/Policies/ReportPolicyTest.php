<?php

use App\Models\Report;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

test('admin and teacher can view reports', function () {
    $admin = User::factory()->admin()->create();
    $teacher = User::factory()->teacher()->create();

    expect(Gate::forUser($admin)->allows('viewAny', Report::class))->toBeTrue()
        ->and(Gate::forUser($teacher)->allows('viewAny', Report::class))->toBeTrue();
});

test('student cannot view school-wide reports but can view own', function () {
    $user = User::factory()->student()->create();
    Student::factory()->create(['user_id' => $user->id]);

    expect(Gate::forUser($user)->denies('viewAny', Report::class))->toBeTrue()
        ->and(Gate::forUser($user)->allows('viewOwn', Report::class))->toBeTrue();
});

test('student without profile cannot view own report', function () {
    $user = User::factory()->student()->create();

    expect(Gate::forUser($user)->denies('viewOwn', Report::class))->toBeTrue();
});
