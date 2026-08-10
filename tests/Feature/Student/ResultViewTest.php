<?php

use App\Enums\GradeLetter;
use App\Models\Exam;
use App\Models\ExamSubject;
use App\Models\Mark;
use App\Models\Student;
use App\Models\User;

test('student can view published results only', function () {
    $user = User::factory()->student()->create();
    [$year, $schoolClass, $subject] = examFixtures();
    $student = Student::factory()->create([
        'user_id' => $user->id,
        'current_class_id' => $schoolClass->id,
    ]);

    $published = Exam::factory()->published()->create([
        'academic_year_id' => $year->id,
        'grade_id' => $schoolClass->grade_id,
        'school_class_id' => $schoolClass->id,
    ]);
    $publishedSubject = ExamSubject::factory()->create([
        'exam_id' => $published->id,
        'subject_id' => $subject->id,
    ]);
    Mark::factory()->create([
        'exam_subject_id' => $publishedSubject->id,
        'student_id' => $student->id,
        'marks_obtained' => 70,
        'grade_letter' => GradeLetter::B,
        'is_pass' => true,
    ]);

    $draft = Exam::factory()->create([
        'academic_year_id' => $year->id,
        'grade_id' => $schoolClass->grade_id,
        'school_class_id' => $schoolClass->id,
        'name' => 'Hidden Draft Exam',
    ]);
    $draftSubject = ExamSubject::factory()->create([
        'exam_id' => $draft->id,
        'subject_id' => $subject->id,
    ]);
    Mark::factory()->create([
        'exam_subject_id' => $draftSubject->id,
        'student_id' => $student->id,
        'marks_obtained' => 90,
        'grade_letter' => GradeLetter::A,
        'is_pass' => true,
    ]);

    $this->actingAs($user)
        ->get(route('student.results'))
        ->assertOk()
        ->assertSee($published->name)
        ->assertDontSee('Hidden Draft Exam');
});

test('teacher cannot open student results page', function () {
    $teacher = User::factory()->teacher()->create();

    $this->actingAs($teacher)
        ->get(route('student.results'))
        ->assertForbidden();
});
