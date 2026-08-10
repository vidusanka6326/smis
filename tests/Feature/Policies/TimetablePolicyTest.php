<?php

use App\Models\ReliefTeacherAssignment;
use App\Models\Teacher;
use App\Models\TimetableEntry;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

test('admin can manage timetable entries via policy', function () {
    $admin = User::factory()->admin()->create();
    $entry = TimetableEntry::factory()->create();

    expect(Gate::forUser($admin)->allows('viewAny', TimetableEntry::class))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('create', TimetableEntry::class))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('view', $entry))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('update', $entry))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('delete', $entry))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('restore', $entry))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('forceDelete', $entry))->toBeTrue();
});

test('teacher can view own timetable entry but not manage', function () {
    $user = User::factory()->teacher()->create();
    $teacher = Teacher::factory()->create(['user_id' => $user->id]);
    $own = TimetableEntry::factory()->create(['teacher_id' => $teacher->id]);
    $other = TimetableEntry::factory()->create();

    expect(Gate::forUser($user)->allows('viewAny', TimetableEntry::class))->toBeTrue()
        ->and(Gate::forUser($user)->allows('view', $own))->toBeTrue()
        ->and(Gate::forUser($user)->denies('view', $other))->toBeTrue()
        ->and(Gate::forUser($user)->denies('create', TimetableEntry::class))->toBeTrue()
        ->and(Gate::forUser($user)->denies('update', $own))->toBeTrue()
        ->and(Gate::forUser($user)->denies('delete', $own))->toBeTrue();
});

test('admin can manage relief assignments via policy', function () {
    $admin = User::factory()->admin()->create();
    $assignment = ReliefTeacherAssignment::factory()->create();

    expect(Gate::forUser($admin)->allows('viewAny', ReliefTeacherAssignment::class))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('create', ReliefTeacherAssignment::class))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('view', $assignment))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('update', $assignment))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('delete', $assignment))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('restore', $assignment))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('forceDelete', $assignment))->toBeTrue();
});

test('teacher cannot create relief assignments', function () {
    $teacher = User::factory()->teacher()->create();

    expect(Gate::forUser($teacher)->denies('create', ReliefTeacherAssignment::class))->toBeTrue();
});
