<?php

use App\Enums\PermissionName;
use App\Enums\RoleName;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

test('roles and permissions are seeded', function () {
    foreach (RoleName::cases() as $role) {
        expect(Role::findByName($role->value))->not->toBeNull();
    }

    foreach (PermissionName::cases() as $permission) {
        expect(Permission::findByName($permission->value))->not->toBeNull();
    }
});

test('admin role receives manage users permission', function () {
    $admin = User::factory()->admin()->create();

    expect($admin->can(PermissionName::ManageUsers->value))->toBeTrue()
        ->and($admin->hasRole(RoleName::Admin))->toBeTrue();
});

test('teacher role cannot manage users', function () {
    $teacher = User::factory()->teacher()->create();

    expect($teacher->can(PermissionName::ManageUsers->value))->toBeFalse()
        ->and($teacher->can(PermissionName::EnterMarks->value))->toBeTrue();
});

test('student role is read-scoped for marks and attendance', function () {
    $student = User::factory()->student()->create();

    expect($student->can(PermissionName::ViewMarks->value))->toBeTrue()
        ->and($student->can(PermissionName::EnterMarks->value))->toBeFalse()
        ->and($student->can(PermissionName::ManageUsers->value))->toBeFalse();
});
