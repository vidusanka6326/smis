<?php

use App\Enums\GradeLetter;
use App\Models\Exam;
use App\Models\ExamSubject;
use App\Models\Mark;
use App\Models\Student;
use App\Models\User;

test('student can view own combined report', function () {
    $user = User::factory()->student()->create();
    [$year, $schoolClass, $subject] = examFixtures();
    $student = Student::factory()->create([
        'user_id' => $user->id,
        'current_class_id' => $schoolClass->id,
    ]);

    $exam = Exam::factory()->published()->create([
        'academic_year_id' => $year->id,
        'grade_id' => $schoolClass->grade_id,
        'school_class_id' => $schoolClass->id,
    ]);
    $examSubject = ExamSubject::factory()->create([
        'exam_id' => $exam->id,
        'subject_id' => $subject->id,
    ]);
    Mark::factory()->create([
        'exam_subject_id' => $examSubject->id,
        'student_id' => $student->id,
        'marks_obtained' => 88,
        'grade_letter' => GradeLetter::A,
        'is_pass' => true,
    ]);

    $this->actingAs($user)
        ->get(route('student.report'))
        ->assertOk()
        ->assertSee(__('Report card'))
        ->assertSee($exam->name)
        ->assertSee(__('Overall exam average'))
        ->assertSee(__('Pass'));
});

test('teacher cannot open student own report', function () {
    $teacher = User::factory()->teacher()->create();

    $this->actingAs($teacher)
        ->get(route('student.report'))
        ->assertForbidden();
});
