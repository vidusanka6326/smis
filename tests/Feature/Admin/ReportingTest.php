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

test('admin can open reports catalog', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.reports.dashboard'))
        ->assertOk()
        ->assertSee(__('Choose a report, filter the data, then download PDF or CSV.'))
        ->assertSee(__('Student attendance'))
        ->assertSee(__('Attendance at risk'))
        ->assertSee(__('Exam results'));
});

test('admin can export demographics csv and pdf', function () {
    $admin = User::factory()->admin()->create();
    [$year, $schoolClass] = examFixtures();
    Student::factory()->create(['current_class_id' => $schoolClass->id]);

    $this->actingAs($admin)
        ->get(route('admin.reports.demographics', ['export' => 'csv']))
        ->assertOk()
        ->assertHeader('content-disposition');

    $pdf = $this->actingAs($admin)
        ->get(route('admin.reports.demographics', ['export' => 'pdf']));

    $pdf->assertOk();
    expect($pdf->headers->get('content-type'))->toStartWith('application/pdf')
        ->and($pdf->getContent())->toStartWith('%PDF');
});

test('admin can view performance rankings for published exam', function () {
    $admin = User::factory()->admin()->create();
    [$year, $schoolClass, $subject, $student] = examFixtures(withStudent: true);
    $other = Student::factory()->create(['current_class_id' => $schoolClass->id]);

    $exam = Exam::factory()->published()->create([
        'academic_year_id' => $year->id,
        'grade_id' => $schoolClass->grade_id,
        'school_class_id' => $schoolClass->id,
    ]);
    $examSubject = ExamSubject::factory()->create([
        'exam_id' => $exam->id,
        'subject_id' => $subject->id,
        'max_marks' => 100,
        'pass_mark' => 40,
    ]);

    Mark::factory()->create([
        'exam_subject_id' => $examSubject->id,
        'student_id' => $student->id,
        'marks_obtained' => 90,
        'grade_letter' => GradeLetter::A,
        'is_pass' => true,
    ]);
    Mark::factory()->create([
        'exam_subject_id' => $examSubject->id,
        'student_id' => $other->id,
        'marks_obtained' => 20,
        'grade_letter' => GradeLetter::F,
        'is_pass' => false,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.reports.performance', ['exam_id' => $exam->id]))
        ->assertOk()
        ->assertSee($student->user->name)
        ->assertSee($other->user->name);
});

test('admin attendance report lists monthly rows', function () {
    $admin = User::factory()->admin()->create();
    [$year, $schoolClass, $subject, $student] = examFixtures(withStudent: true);

    $session = AttendanceSession::factory()->forClass($schoolClass)->create([
        'date' => now()->toDateString(),
    ]);
    StudentAttendance::factory()->create([
        'attendance_session_id' => $session->id,
        'student_id' => $student->id,
        'status' => AttendanceStatus::Present,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.reports.attendance', ['month' => now()->format('Y-m')]))
        ->assertOk()
        ->assertSee($student->user->name);
});

test('teacher cannot open admin reports', function () {
    $teacher = User::factory()->teacher()->create();

    $this->actingAs($teacher)
        ->get(route('admin.reports.dashboard'))
        ->assertForbidden();
});
