<?php

use App\Enums\RoleName;
use App\Models\Exam;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TimetableEntry;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\SriLankanDemoCatalog;

test('database seeder populates a full sri lankan demo school', function () {
    $this->seed(DatabaseSeeder::class);

    expect(User::query()->role(RoleName::Admin)->count())->toBe(1)
        ->and(User::query()->role(RoleName::Officer)->count())->toBe(5)
        ->and(Teacher::query()->count())->toBe(30)
        ->and(Student::query()->count())->toBe(600)
        ->and(SchoolClass::query()->count())->toBe(28);

    $grade6 = SchoolClass::query()->where('code', '6-A')->withCount('subjects')->first();
    $grade10 = SchoolClass::query()->where('code', '10-A')->with(['subjects', 'classTeacher.user'])->first();
    $physical = SchoolClass::query()->where('code', '12-SCI-A')->with('subjects')->first();
    $bio = SchoolClass::query()->where('code', '12-SCI-B')->with('subjects')->first();

    expect($grade6?->subjects_count)->toBe(12)
        ->and($grade10?->subjects)->toHaveCount(9)
        ->and($physical?->subjects->pluck('code')->sort()->values()->all())->toBe(['CHE', 'CMAT', 'PHY'])
        ->and($bio?->subjects->pluck('code')->sort()->values()->all())->toBe(['BIO', 'CHE', 'PHY'])
        ->and($grade10?->classTeacher?->user?->email)->toBe(SriLankanDemoCatalog::CLASS_TEACHER_EMAIL);

    expect(User::query()->where('email', SriLankanDemoCatalog::ADMIN_EMAIL)->exists())->toBeTrue()
        ->and(User::query()->where('email', SriLankanDemoCatalog::SUBJECT_TEACHER_EMAIL)->exists())->toBeTrue()
        ->and(Student::query()->where('admission_no', SriLankanDemoCatalog::DEMO_ADMISSION_NO)->exists())->toBeTrue()
        ->and(TimetableEntry::query()->count())->toBeGreaterThan(500)
        ->and(Exam::query()->whereNotNull('published_at')->count())->toBeGreaterThan(5);

    $classTeacherIds = SchoolClass::query()->whereNotNull('class_teacher_id')->pluck('class_teacher_id');

    expect($classTeacherIds)->toHaveCount(25)
        ->and($classTeacherIds->unique())->toHaveCount(25)
        ->and(SchoolClass::query()->whereNull('class_teacher_id')->count())->toBe(3);
})->group('seeders');
