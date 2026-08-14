<?php

use App\Enums\AttendanceStatus;
use App\Enums\GradeLetter;
use App\Models\AttendanceSession;
use App\Models\Exam;
use App\Models\ExamSubject;
use App\Models\Mark;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\User;

test('student can open reports catalog', function () {
    $user = User::factory()->student()->create();
    Student::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->get(route('student.reports'))
        ->assertOk()
        ->assertSee(__('My reports'))
        ->assertSee(__('Report card'))
        ->assertSee(__('My attendance'))
        ->assertSee(__('My exam results'));
});

test('student can download attendance report pdf', function () {
    $user = User::factory()->student()->create();
    [$year, $schoolClass] = examFixtures();
    $student = Student::factory()->create([
        'user_id' => $user->id,
        'current_class_id' => $schoolClass->id,
    ]);
    $session = AttendanceSession::factory()->forClass($schoolClass)->create([
        'date' => now()->toDateString(),
    ]);
    StudentAttendance::factory()->create([
        'attendance_session_id' => $session->id,
        'student_id' => $student->id,
        'status' => AttendanceStatus::Present,
    ]);

    $this->actingAs($user)
        ->get(route('student.reports.attendance', ['month' => now()->format('Y-m')]))
        ->assertOk()
        ->assertSee(__('Download PDF'));

    $pdf = $this->actingAs($user)
        ->get(route('student.reports.attendance', [
            'month' => now()->format('Y-m'),
            'export' => 'pdf',
        ]));

    $pdf->assertOk();
    expect($pdf->headers->get('content-type'))->toStartWith('application/pdf');
});

test('student can download exam results csv', function () {
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
        'marks_obtained' => 70,
        'grade_letter' => GradeLetter::B,
        'is_pass' => true,
    ]);

    $this->actingAs($user)
        ->get(route('student.reports.results', ['export' => 'csv']))
        ->assertOk()
        ->assertHeader('content-disposition');
});

test('student without a profile cannot open the reports catalog', function () {
    $user = User::factory()->student()->create();

    $this->actingAs($user)
        ->get(route('student.reports'))
        ->assertForbidden();
});

test('teacher cannot open student report catalog', function () {
    $teacher = User::factory()->teacher()->create();

    $this->actingAs($teacher)
        ->get(route('student.reports'))
        ->assertForbidden();
});
