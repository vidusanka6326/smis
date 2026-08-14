<?php

use App\Enums\TeacherAssignmentRole;
use App\Models\AcademicYear;
use App\Models\Exam;
use App\Models\ExamSubject;
use App\Models\Grade;
use App\Models\Mark;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use App\Models\User;

test('class teacher can open scoped reports dashboard', function () {
    $user = User::factory()->teacher()->create();
    $teacher = Teacher::factory()->create(['user_id' => $user->id]);
    $year = AcademicYear::factory()->current()->create();
    $grade = Grade::factory()->number(10)->create();
    $schoolClass = SchoolClass::factory()->create([
        'academic_year_id' => $year->id,
        'grade_id' => $grade->id,
        'class_teacher_id' => $teacher->id,
    ]);
    TeacherAssignment::factory()->create([
        'teacher_id' => $teacher->id,
        'school_class_id' => $schoolClass->id,
        'academic_year_id' => $year->id,
        'role_in_assignment' => TeacherAssignmentRole::ClassTeacher,
        'subject_id' => null,
    ]);
    Student::factory()->create(['current_class_id' => $schoolClass->id]);

    $this->actingAs($user)
        ->get(route('teacher.reports.dashboard'))
        ->assertOk()
        ->assertSee(__('Reports'))
        ->assertSee(__('Student attendance'))
        ->assertSee(__('Class roster'));
});

test('subject teacher can view performance for assigned subject students', function () {
    $user = User::factory()->teacher()->create();
    $teacher = Teacher::factory()->create(['user_id' => $user->id]);
    $year = AcademicYear::factory()->current()->create();
    $grade = Grade::factory()->number(10)->create();
    $subject = Subject::factory()->forGradeRange(1, 13)->create();
    $schoolClass = SchoolClass::factory()->create([
        'academic_year_id' => $year->id,
        'grade_id' => $grade->id,
    ]);
    $schoolClass->subjects()->sync([$subject->id]);
    $student = Student::factory()->create(['current_class_id' => $schoolClass->id]);

    TeacherAssignment::factory()->create([
        'teacher_id' => $teacher->id,
        'school_class_id' => $schoolClass->id,
        'subject_id' => $subject->id,
        'academic_year_id' => $year->id,
        'role_in_assignment' => TeacherAssignmentRole::SubjectTeacher,
    ]);

    $exam = Exam::factory()->published()->create([
        'academic_year_id' => $year->id,
        'grade_id' => $grade->id,
        'school_class_id' => $schoolClass->id,
    ]);
    $examSubject = ExamSubject::factory()->create([
        'exam_id' => $exam->id,
        'subject_id' => $subject->id,
    ]);
    Mark::factory()->create([
        'exam_subject_id' => $examSubject->id,
        'student_id' => $student->id,
        'marks_obtained' => 70,
    ]);

    $this->actingAs($user)
        ->get(route('teacher.reports.performance', ['exam_id' => $exam->id, 'subject_id' => $subject->id]))
        ->assertOk()
        ->assertSee($student->user->name);
});

test('student cannot open teacher reports', function () {
    $student = User::factory()->student()->create();

    $this->actingAs($student)
        ->get(route('teacher.reports.dashboard'))
        ->assertForbidden();
});
