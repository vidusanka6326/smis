<?php

use App\Enums\ExamType;
use App\Enums\Gender;
use App\Enums\UserStatus;
use App\Models\AcademicYear;
use App\Models\Exam;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use App\Support\ListQuery;

test('admin student index paginates and keeps filters in the query string', function () {
    $admin = User::factory()->admin()->create();
    $year = AcademicYear::factory()->create();
    $grade = Grade::factory()->number(7)->create();
    $schoolClass = SchoolClass::factory()->create([
        'academic_year_id' => $year->id,
        'grade_id' => $grade->id,
        'code' => '7-A',
        'name' => 'A',
    ]);

    Student::factory()->count(21)->create([
        'gender' => Gender::Girl,
        'current_class_id' => $schoolClass->id,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.students.index', [
            'gender' => Gender::Girl->value,
            'per_page' => 10,
            'page' => 2,
        ]))
        ->assertOk()
        ->assertSee(__('Showing'))
        ->assertSee('11')
        ->assertSee('20')
        ->assertSee(__('Apply'))
        ->assertSee(__('Per page'))
        ->assertSee('data-flux-select', false);
});

test('admin can filter teachers by search', function () {
    $admin = User::factory()->admin()->create();
    $match = Teacher::factory()->create(['employee_no' => 'TCH-FIND']);
    Teacher::factory()->create(['employee_no' => 'TCH-HIDE']);

    $this->actingAs($admin)
        ->get(route('admin.teachers.index', ['search' => 'TCH-FIND']))
        ->assertOk()
        ->assertSee($match->user->name)
        ->assertDontSee('TCH-HIDE')
        ->assertSee(__('Clear'));
});

test('admin can filter classes by grade', function () {
    $admin = User::factory()->admin()->create();
    $year = AcademicYear::factory()->create();
    $gradeSix = Grade::factory()->number(6)->create();
    $gradeSeven = Grade::factory()->number(7)->create();
    $keep = SchoolClass::factory()->create([
        'academic_year_id' => $year->id,
        'grade_id' => $gradeSix->id,
        'code' => '6-KEEP',
        'name' => 'KEEP',
    ]);
    SchoolClass::factory()->create([
        'academic_year_id' => $year->id,
        'grade_id' => $gradeSeven->id,
        'code' => '7-SKIP',
        'name' => 'SKIP',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.classes.index', ['grade_id' => $gradeSix->id]))
        ->assertOk()
        ->assertSee($keep->code)
        ->assertDontSee('7-SKIP');
});

test('admin can filter exams by type and officers by status', function () {
    $admin = User::factory()->admin()->create();
    $year = AcademicYear::factory()->create();
    $term = Exam::factory()->create([
        'academic_year_id' => $year->id,
        'type' => ExamType::TermTest,
        'name' => 'Visible Term Test',
    ]);
    Exam::factory()->create([
        'academic_year_id' => $year->id,
        'type' => ExamType::Ol,
        'name' => 'Hidden OL Paper',
    ]);

    $active = User::factory()->officer()->create([
        'name' => 'Active Officer',
        'status' => UserStatus::Active,
    ]);
    User::factory()->officer()->create([
        'name' => 'Inactive Officer',
        'status' => UserStatus::Inactive,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.exams.index', ['type' => ExamType::TermTest->value]))
        ->assertOk()
        ->assertSee($term->name)
        ->assertDontSee('Hidden OL Paper');

    $this->actingAs($admin)
        ->get(route('admin.officers.index', ['status' => UserStatus::Active->value]))
        ->assertOk()
        ->assertSee($active->name)
        ->assertDontSee('Inactive Officer');
});

test('student results and teacher students lists render the shared filter bar', function () {
    $teacherUser = User::factory()->teacher()->create();
    $teacher = Teacher::factory()->create(['user_id' => $teacherUser->id]);
    $year = AcademicYear::factory()->current()->create();
    $grade = Grade::factory()->number(8)->create();
    $schoolClass = SchoolClass::factory()->create([
        'academic_year_id' => $year->id,
        'grade_id' => $grade->id,
        'class_teacher_id' => $teacher->id,
    ]);
    Student::factory()->create(['current_class_id' => $schoolClass->id]);

    $this->actingAs($teacherUser)
        ->get(route('teacher.students.index'))
        ->assertOk()
        ->assertSee(__('Apply'))
        ->assertSee(__('Per page'));

    $studentUser = User::factory()->student()->create();
    Student::factory()->create([
        'user_id' => $studentUser->id,
        'current_class_id' => $schoolClass->id,
    ]);

    $this->actingAs($studentUser)
        ->get(route('student.results'))
        ->assertOk()
        ->assertSee(__('Apply'));
});

test('list query per page options are the sizes shown in the ui', function () {
    expect(ListQuery::PER_PAGE_OPTIONS)->toBe([10, 20, 50, 100]);
});
