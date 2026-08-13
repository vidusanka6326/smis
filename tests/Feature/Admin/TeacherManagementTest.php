<?php

use App\Enums\TeacherAssignmentRole;
use App\Enums\UserStatus;
use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use App\Models\User;

test('admin can create and view a teacher', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('admin.teachers.store'), [
            'name' => 'New Teacher',
            'email' => 'new.teacher@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'status' => UserStatus::Active->value,
            'employee_no' => 'TCH-9001',
            'phone' => '0711111111',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    $teacher = Teacher::query()->where('employee_no', 'TCH-9001')->first();

    expect($teacher)->not->toBeNull()
        ->and($teacher->user->hasRole('teacher'))->toBeTrue();

    $this->actingAs($admin)
        ->get(route('admin.teachers.show', $teacher))
        ->assertOk()
        ->assertSee('New Teacher');
});

test('admin assignment form uses flux selects for class and role', function () {
    $admin = User::factory()->admin()->create();
    $teacher = Teacher::factory()->create();
    $year = AcademicYear::factory()->current()->create();
    $grade = Grade::factory()->number(10)->create();
    $schoolClass = SchoolClass::factory()->create([
        'academic_year_id' => $year->id,
        'grade_id' => $grade->id,
        'name' => 'A',
        'code' => '10-A',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.teachers.assignments.edit', $teacher))
        ->assertOk()
        ->assertSee('data-flux-select-native', false)
        ->assertSee('data-flux-dropdown', false)
        ->assertSee($schoolClass->code)
        ->assertSee(TeacherAssignmentRole::ClassTeacher->label())
        ->assertDontSee('<select class="mt-1 w-full rounded-lg', false);
});

test('admin can sync teacher assignments including subject teacher', function () {
    $admin = User::factory()->admin()->create();
    $teacher = Teacher::factory()->create();
    $year = AcademicYear::factory()->create();
    $grade = Grade::factory()->number(10)->create();
    $schoolClass = SchoolClass::factory()->create([
        'academic_year_id' => $year->id,
        'grade_id' => $grade->id,
        'name' => 'A',
        'code' => '10-A',
    ]);
    $subject = Subject::factory()->forGradeRange(1, 13)->create();
    $schoolClass->subjects()->attach($subject);

    $this->actingAs($admin)
        ->put(route('admin.teachers.assignments.update', $teacher), [
            'academic_year_id' => $year->id,
            'assignments' => [
                [
                    'school_class_id' => $schoolClass->id,
                    'subject_id' => null,
                    'role_in_assignment' => TeacherAssignmentRole::ClassTeacher->value,
                ],
                [
                    'school_class_id' => $schoolClass->id,
                    'subject_id' => $subject->id,
                    'role_in_assignment' => TeacherAssignmentRole::SubjectTeacher->value,
                ],
            ],
        ])
        ->assertSessionHasNoErrors();

    expect(TeacherAssignment::query()->where('teacher_id', $teacher->id)->count())->toBe(2)
        ->and($schoolClass->fresh()->class_teacher_id)->toBe($teacher->id);
});

test('teacher cannot manage other teachers', function () {
    $teacherUser = User::factory()->teacher()->create();
    Teacher::factory()->create(['user_id' => $teacherUser->id]);

    $this->actingAs($teacherUser)
        ->get(route('admin.teachers.index'))
        ->assertForbidden();
});
