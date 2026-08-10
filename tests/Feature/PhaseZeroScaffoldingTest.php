<?php

use Illuminate\Support\Facades\Schema;

test('phase 0 scaffolding artifacts exist', function () {
    expect(file_exists(base_path('docs/PROJECT_STATUS.md')))->toBeTrue()
        ->and(file_exists(base_path('docs/architecture/overview.md')))->toBeTrue()
        ->and(file_exists(base_path('docs/architecture/er-diagram.md')))->toBeTrue()
        ->and(file_exists(base_path('docs/modules/auth.md')))->toBeTrue()
        ->and(file_exists(base_path('docs/modules/admin.md')))->toBeTrue()
        ->and(file_exists(base_path('docs/modules/teacher.md')))->toBeTrue()
        ->and(file_exists(base_path('docs/modules/student.md')))->toBeTrue()
        ->and(file_exists(base_path('docs/modules/attendance.md')))->toBeTrue()
        ->and(file_exists(base_path('docs/modules/timetable.md')))->toBeTrue()
        ->and(file_exists(base_path('docs/modules/examination.md')))->toBeTrue()
        ->and(file_exists(base_path('docs/modules/reporting.md')))->toBeTrue()
        ->and(file_exists(base_path('docs/api/README.md')))->toBeTrue()
        ->and(file_exists(base_path('docs/decisions/0001-use-spatie-permission.md')))->toBeTrue()
        ->and(file_exists(base_path('docs/testing/strategy.md')))->toBeTrue()
        ->and(file_exists(base_path('docs/setup/local-development.md')))->toBeTrue()
        ->and(file_exists(base_path('.cursor/rules/project.mdc')))->toBeTrue()
        ->and(file_exists(base_path('CHANGELOG.md')))->toBeTrue()
        ->and(file_exists(base_path('config/permission.php')))->toBeTrue()
        ->and(is_dir(base_path('app/Actions')))->toBeTrue()
        ->and(is_dir(base_path('app/Services')))->toBeTrue()
        ->and(is_dir(base_path('app/Policies')))->toBeTrue()
        ->and(is_dir(base_path('app/Enums')))->toBeTrue();
});

test('spatie permission migration is present and migrates cleanly', function () {
    $migrations = collect(glob(database_path('migrations/*_create_permission_tables.php')));

    expect($migrations)->not->toBeEmpty();

    expect(Schema::hasTable('roles'))->toBeTrue()
        ->and(Schema::hasTable('permissions'))->toBeTrue()
        ->and(Schema::hasTable('model_has_roles'))->toBeTrue()
        ->and(Schema::hasTable('role_has_permissions'))->toBeTrue();
});
