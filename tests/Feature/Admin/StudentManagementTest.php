<?php

use App\Enums\Gender;
use App\Enums\UserStatus;
use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;

test('admin can create a student with enrollment', function () {
    $admin = User::factory()->admin()->create();
    $year = AcademicYear::factory()->create();
    $grade = Grade::factory()->number(8)->create();
    $schoolClass = SchoolClass::factory()->create([
        'academic_year_id' => $year->id,
        'grade_id' => $grade->id,
        'name' => 'A',
        'code' => '8-A',
    ]);

    $this->actingAs($admin)
        ->post(route('admin.students.store'), [
            'name' => 'Ada Student',
            'email' => 'ada.student@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'status' => UserStatus::Active->value,
            'admission_no' => 'ADM-8001',
            'date_of_birth' => '2014-01-01',
            'gender' => Gender::Girl->value,
            'guardian_name' => 'Parent',
            'academic_year_id' => $year->id,
            'school_class_id' => $schoolClass->id,
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    $student = Student::query()->where('admission_no', 'ADM-8001')->first();

    expect($student)->not->toBeNull()
        ->and($student->current_class_id)->toBe($schoolClass->id)
        ->and($student->enrollments()->count())->toBe(1)
        ->and($student->user->hasRole('student'))->toBeTrue();
});

test('admin can filter students by gender grade class and subject', function () {
    $admin = User::factory()->admin()->create();
    $year = AcademicYear::factory()->create();
    $grade = Grade::factory()->number(9)->create();
    $schoolClass = SchoolClass::factory()->create([
        'academic_year_id' => $year->id,
        'grade_id' => $grade->id,
        'code' => '9-A',
        'name' => 'A',
    ]);
    $subject = Subject::factory()->forGradeRange(1, 13)->create();
    $schoolClass->subjects()->attach($subject);

    $girl = Student::factory()->create([
        'gender' => Gender::Girl,
        'current_class_id' => $schoolClass->id,
        'admission_no' => 'ADM-GIRL',
    ]);
    Student::factory()->create([
        'gender' => Gender::Boy,
        'current_class_id' => $schoolClass->id,
        'admission_no' => 'ADM-BOY',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.students.index', [
            'gender' => Gender::Girl->value,
            'grade_id' => $grade->id,
            'class_id' => $schoolClass->id,
            'subject_id' => $subject->id,
        ]))
        ->assertOk()
        ->assertSee($girl->user->name)
        ->assertDontSee('ADM-BOY');
});

test('teacher cannot access admin student routes', function () {
    $teacher = User::factory()->teacher()->create();

    $this->actingAs($teacher)
        ->get(route('admin.students.index'))
        ->assertForbidden();
});
