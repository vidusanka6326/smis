<?php

use App\Enums\TeacherAssignmentRole;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

test('admin can fully manage students via policy', function () {
    $admin = User::factory()->admin()->create();
    $student = Student::factory()->create();

    expect(Gate::forUser($admin)->allows('viewAny', Student::class))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('create', Student::class))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('view', $student))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('update', $student))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('delete', $student))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('restore', $student))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('forceDelete', $student))->toBeTrue();
});

test('class teacher can manage students in own class only', function () {
    $user = User::factory()->teacher()->create();
    $teacher = Teacher::factory()->create(['user_id' => $user->id]);
    $ownClass = SchoolClass::factory()->create(['class_teacher_id' => $teacher->id]);
    $otherClass = SchoolClass::factory()->create();

    TeacherAssignment::factory()->create([
        'teacher_id' => $teacher->id,
        'school_class_id' => $ownClass->id,
        'academic_year_id' => $ownClass->academic_year_id,
        'role_in_assignment' => TeacherAssignmentRole::ClassTeacher,
        'subject_id' => null,
    ]);

    $ownStudent = Student::factory()->create(['current_class_id' => $ownClass->id]);
    $otherStudent = Student::factory()->create(['current_class_id' => $otherClass->id]);

    expect(Gate::forUser($user)->allows('viewAny', Student::class))->toBeTrue()
        ->and(Gate::forUser($user)->allows('create', Student::class))->toBeTrue()
        ->and(Gate::forUser($user)->allows('createInClass', [Student::class, $ownClass]))->toBeTrue()
        ->and(Gate::forUser($user)->denies('createInClass', [Student::class, $otherClass]))->toBeTrue()
        ->and(Gate::forUser($user)->allows('view', $ownStudent))->toBeTrue()
        ->and(Gate::forUser($user)->allows('update', $ownStudent))->toBeTrue()
        ->and(Gate::forUser($user)->denies('view', $otherStudent))->toBeTrue()
        ->and(Gate::forUser($user)->denies('delete', $ownStudent))->toBeTrue();
});

test('student can view own profile only', function () {
    $user = User::factory()->student()->create();
    $own = Student::factory()->create(['user_id' => $user->id]);
    $other = Student::factory()->create();

    expect(Gate::forUser($user)->allows('view', $own))->toBeTrue()
        ->and(Gate::forUser($user)->denies('view', $other))->toBeTrue()
        ->and(Gate::forUser($user)->denies('create', Student::class))->toBeTrue()
        ->and(Gate::forUser($user)->denies('update', $own))->toBeTrue();
});
