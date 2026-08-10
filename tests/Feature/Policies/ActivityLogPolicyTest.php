<?php

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

test('admin can view activity logs', function () {
    $admin = User::factory()->admin()->create();
    $log = ActivityLog::factory()->create(['causer_id' => $admin->id]);

    expect(Gate::forUser($admin)->allows('viewAny', ActivityLog::class))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('view', $log))->toBeTrue();
});

test('teacher cannot view activity logs', function () {
    $teacher = User::factory()->teacher()->create();
    $log = ActivityLog::factory()->create();

    expect(Gate::forUser($teacher)->denies('viewAny', ActivityLog::class))->toBeTrue()
        ->and(Gate::forUser($teacher)->denies('view', $log))->toBeTrue();
});

test('student cannot view activity logs', function () {
    $student = User::factory()->student()->create();
    $log = ActivityLog::factory()->create();

    expect(Gate::forUser($student)->denies('viewAny', ActivityLog::class))->toBeTrue()
        ->and(Gate::forUser($student)->denies('view', $log))->toBeTrue();
});
