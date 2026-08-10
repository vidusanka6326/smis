<?php

use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Stream;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

$models = [
    'academic year' => AcademicYear::class,
    'grade' => Grade::class,
    'stream' => Stream::class,
    'subject' => Subject::class,
    'school class' => SchoolClass::class,
];

foreach ($models as $label => $modelClass) {
    test("admin can manage {$label} via policy", function () use ($modelClass) {
        $admin = User::factory()->admin()->create();
        $model = $modelClass::factory()->create();

        expect(Gate::forUser($admin)->allows('viewAny', $modelClass))->toBeTrue()
            ->and(Gate::forUser($admin)->allows('create', $modelClass))->toBeTrue()
            ->and(Gate::forUser($admin)->allows('view', $model))->toBeTrue()
            ->and(Gate::forUser($admin)->allows('update', $model))->toBeTrue()
            ->and(Gate::forUser($admin)->allows('delete', $model))->toBeTrue()
            ->and(Gate::forUser($admin)->allows('restore', $model))->toBeTrue()
            ->and(Gate::forUser($admin)->allows('forceDelete', $model))->toBeTrue();
    });

    test("teacher cannot manage {$label} via policy", function () use ($modelClass) {
        $teacher = User::factory()->teacher()->create();
        $model = $modelClass::factory()->create();

        expect(Gate::forUser($teacher)->denies('viewAny', $modelClass))->toBeTrue()
            ->and(Gate::forUser($teacher)->denies('create', $modelClass))->toBeTrue()
            ->and(Gate::forUser($teacher)->denies('view', $model))->toBeTrue()
            ->and(Gate::forUser($teacher)->denies('update', $model))->toBeTrue()
            ->and(Gate::forUser($teacher)->denies('delete', $model))->toBeTrue()
            ->and(Gate::forUser($teacher)->denies('restore', $model))->toBeTrue()
            ->and(Gate::forUser($teacher)->denies('forceDelete', $model))->toBeTrue();
    });

    test("student cannot manage {$label} via policy", function () use ($modelClass) {
        $student = User::factory()->student()->create();
        $model = $modelClass::factory()->create();

        expect(Gate::forUser($student)->denies('viewAny', $modelClass))->toBeTrue()
            ->and(Gate::forUser($student)->denies('create', $modelClass))->toBeTrue()
            ->and(Gate::forUser($student)->denies('view', $model))->toBeTrue()
            ->and(Gate::forUser($student)->denies('update', $model))->toBeTrue()
            ->and(Gate::forUser($student)->denies('delete', $model))->toBeTrue()
            ->and(Gate::forUser($student)->denies('restore', $model))->toBeTrue()
            ->and(Gate::forUser($student)->denies('forceDelete', $model))->toBeTrue();
    });
}
