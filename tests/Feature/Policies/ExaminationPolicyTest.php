<?php

use App\Enums\TeacherAssignmentRole;
use App\Models\Exam;
use App\Models\ExamSubject;
use App\Models\Mark;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

test('admin can manage exams and publish', function () {
    $admin = User::factory()->admin()->create();
    $exam = Exam::factory()->create();

    expect(Gate::forUser($admin)->allows('create', Exam::class))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('publish', $exam))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('delete', $exam))->toBeTrue();
});

test('class teacher can enter marks for own class subject', function () {
    $user = User::factory()->teacher()->create();
    $teacher = Teacher::factory()->create(['user_id' => $user->id]);
    $exam = Exam::factory()->create();
    $schoolClass = SchoolClass::factory()->create([
        'academic_year_id' => $exam->academic_year_id,
        'grade_id' => $exam->grade_id,
        'class_teacher_id' => $teacher->id,
    ]);
    $exam->update(['school_class_id' => $schoolClass->id]);
    $subject = Subject::factory()->forGradeRange(1, 13)->create();
    $examSubject = ExamSubject::factory()->create([
        'exam_id' => $exam->id,
        'subject_id' => $subject->id,
    ]);

    TeacherAssignment::factory()->create([
        'teacher_id' => $teacher->id,
        'school_class_id' => $schoolClass->id,
        'academic_year_id' => $exam->academic_year_id,
        'role_in_assignment' => TeacherAssignmentRole::ClassTeacher,
        'subject_id' => null,
    ]);

    expect(Gate::forUser($user)->allows('enterMarks', $examSubject))->toBeTrue();
});

test('student can view own published mark only', function () {
    $user = User::factory()->student()->create();
    $student = Student::factory()->create(['user_id' => $user->id]);
    $exam = Exam::factory()->published()->create();
    $examSubject = ExamSubject::factory()->create(['exam_id' => $exam->id]);
    $own = Mark::factory()->create([
        'exam_subject_id' => $examSubject->id,
        'student_id' => $student->id,
    ]);
    $other = Mark::factory()->create();

    expect(Gate::forUser($user)->allows('view', $own))->toBeTrue()
        ->and(Gate::forUser($user)->denies('view', $other))->toBeTrue()
        ->and(Gate::forUser($user)->denies('create', Exam::class))->toBeTrue();
});

test('enter marks denied when exam published', function () {
    $admin = User::factory()->admin()->create();
    $exam = Exam::factory()->published()->create();
    $examSubject = ExamSubject::factory()->create(['exam_id' => $exam->id]);

    expect(Gate::forUser($admin)->denies('enterMarks', $examSubject))->toBeTrue();
});
