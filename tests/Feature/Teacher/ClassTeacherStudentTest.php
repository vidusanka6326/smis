<?php

use App\Enums\Gender;
use App\Enums\TeacherAssignmentRole;
use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use App\Models\User;

test('class teacher can create a student in their own class', function () {
    $teacherUser = User::factory()->teacher()->create();
    $teacher = Teacher::factory()->create(['user_id' => $teacherUser->id]);
    $year = AcademicYear::factory()->create();
    $grade = Grade::factory()->number(7)->create();
    $schoolClass = SchoolClass::factory()->create([
        'academic_year_id' => $year->id,
        'grade_id' => $grade->id,
        'class_teacher_id' => $teacher->id,
        'name' => 'A',
        'code' => '7-A',
    ]);

    TeacherAssignment::factory()->create([
        'teacher_id' => $teacher->id,
        'school_class_id' => $schoolClass->id,
        'academic_year_id' => $year->id,
        'role_in_assignment' => TeacherAssignmentRole::ClassTeacher,
        'subject_id' => null,
    ]);

    $this->actingAs($teacherUser)
        ->post(route('teacher.students.store'), [
            'name' => 'Class Kid',
            'email' => 'class.kid@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'admission_no' => 'ADM-7001',
            'gender' => Gender::Boy->value,
            'academic_year_id' => $year->id,
            'school_class_id' => $schoolClass->id,
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('teacher.students.index'));

    expect(Student::query()->where('admission_no', 'ADM-7001')->exists())->toBeTrue();
});

test('class teacher cannot create a student in another class', function () {
    $teacherUser = User::factory()->teacher()->create();
    $teacher = Teacher::factory()->create(['user_id' => $teacherUser->id]);
    $year = AcademicYear::factory()->create();
    $ownClass = SchoolClass::factory()->create([
        'academic_year_id' => $year->id,
        'class_teacher_id' => $teacher->id,
    ]);
    $otherClass = SchoolClass::factory()->create([
        'academic_year_id' => $year->id,
    ]);

    TeacherAssignment::factory()->create([
        'teacher_id' => $teacher->id,
        'school_class_id' => $ownClass->id,
        'academic_year_id' => $year->id,
        'role_in_assignment' => TeacherAssignmentRole::ClassTeacher,
        'subject_id' => null,
    ]);

    $this->actingAs($teacherUser)
        ->post(route('teacher.students.store'), [
            'name' => 'Other Kid',
            'email' => 'other.kid@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'admission_no' => 'ADM-7002',
            'gender' => Gender::Girl->value,
            'academic_year_id' => $year->id,
            'school_class_id' => $otherClass->id,
        ])
        ->assertSessionHasErrors(['school_class_id']);
});

test('subject teacher without class teacher role cannot create students', function () {
    $teacherUser = User::factory()->teacher()->create();
    Teacher::factory()->create(['user_id' => $teacherUser->id]);

    $this->actingAs($teacherUser)
        ->get(route('teacher.students.create'))
        ->assertForbidden();
});
