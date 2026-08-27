<?php

use App\Enums\DayOfWeek;
use App\Models\AcademicYear;
use App\Models\Exam;
use App\Models\ExamSubject;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TimetableEntry;
use App\Models\User;

test('student can view the exam schedule for their class', function () {
    $user = User::factory()->student()->create();
    $year = AcademicYear::factory()->current()->create();
    $grade = Grade::factory()->number(5)->create();
    $subject = Subject::factory()->forGradeRange(1, 13)->create(['name' => 'Mathematics']);
    $schoolClass = SchoolClass::factory()->create([
        'academic_year_id' => $year->id,
        'grade_id' => $grade->id,
        'code' => '5-S',
    ]);
    Student::factory()->create([
        'user_id' => $user->id,
        'current_class_id' => $schoolClass->id,
    ]);

    $plannedExam = Exam::factory()->create([
        'name' => 'Second Term Examination',
        'academic_year_id' => $year->id,
        'grade_id' => $grade->id,
        'starts_on' => '2026-09-14',
        'ends_on' => '2026-09-18',
    ]);
    ExamSubject::factory()->create([
        'exam_id' => $plannedExam->id,
        'subject_id' => $subject->id,
    ]);

    $otherGrade = Grade::factory()->number(6)->create();
    $otherExam = Exam::factory()->create([
        'name' => 'Other Grade Examination',
        'academic_year_id' => $year->id,
        'grade_id' => $otherGrade->id,
        'starts_on' => '2026-09-21',
    ]);

    $this->actingAs($user)
        ->get(route('student.exam-schedule'))
        ->assertOk()
        ->assertSee($plannedExam->name)
        ->assertSee('14 Sep 2026')
        ->assertSee('Mathematics')
        ->assertDontSee($otherExam->name);
});

test('student can view their class timetable', function () {
    $user = User::factory()->student()->create();
    $year = AcademicYear::factory()->current()->create();
    $grade = Grade::factory()->number(5)->create();
    $subject = Subject::factory()->forGradeRange(1, 13)->create(['name' => 'Science Lab']);
    $schoolClass = SchoolClass::factory()->create([
        'academic_year_id' => $year->id,
        'grade_id' => $grade->id,
        'code' => '5-B',
    ]);
    $schoolClass->subjects()->sync([$subject->id]);
    Student::factory()->create([
        'user_id' => $user->id,
        'current_class_id' => $schoolClass->id,
    ]);
    $teacher = Teacher::factory()->create();

    TimetableEntry::factory()->create([
        'academic_year_id' => $year->id,
        'school_class_id' => $schoolClass->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'day_of_week' => DayOfWeek::Friday,
        'period_number' => 2,
    ]);

    $this->actingAs($user)
        ->get(route('student.timetable'))
        ->assertOk()
        ->assertSee('Science Lab');
});

test('student without a class cannot view timetable', function () {
    $user = User::factory()->student()->create();
    Student::factory()->create([
        'user_id' => $user->id,
        'current_class_id' => null,
    ]);

    $this->actingAs($user)
        ->get(route('student.timetable'))
        ->assertForbidden();
});
