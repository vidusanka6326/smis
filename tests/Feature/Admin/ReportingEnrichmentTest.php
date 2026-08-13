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

test('admin attendance report highlights students below eighty percent', function () {
    $admin = User::factory()->admin()->create();
    [$year, $schoolClass, $subject, $student] = examFixtures(withStudent: true);
    $other = Student::factory()->create(['current_class_id' => $schoolClass->id]);

    $presentSession = AttendanceSession::factory()->forClass($schoolClass)->create([
        'date' => now()->startOfMonth()->toDateString(),
    ]);
    $absentSession = AttendanceSession::factory()->forClass($schoolClass)->create([
        'date' => now()->startOfMonth()->addDay()->toDateString(),
    ]);

    StudentAttendance::factory()->create([
        'attendance_session_id' => $presentSession->id,
        'student_id' => $student->id,
        'status' => AttendanceStatus::Present,
    ]);
    StudentAttendance::factory()->create([
        'attendance_session_id' => $absentSession->id,
        'student_id' => $student->id,
        'status' => AttendanceStatus::Present,
    ]);

    StudentAttendance::factory()->create([
        'attendance_session_id' => $presentSession->id,
        'student_id' => $other->id,
        'status' => AttendanceStatus::Absent,
    ]);
    StudentAttendance::factory()->create([
        'attendance_session_id' => $absentSession->id,
        'student_id' => $other->id,
        'status' => AttendanceStatus::Absent,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.reports.attendance', ['month' => now()->format('Y-m')]))
        ->assertOk()
        ->assertSee(__('Needs attention (below :pct%)', ['pct' => 80]))
        ->assertSee($other->user->name);
});

test('admin examination report shows class comparison', function () {
    $admin = User::factory()->admin()->create();
    [$year, $schoolClass, $subject, $student] = examFixtures(withStudent: true);

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
        'marks_obtained' => 75,
        'grade_letter' => GradeLetter::A,
        'is_pass' => true,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.reports.examination', ['exam_id' => $exam->id]))
        ->assertOk()
        ->assertSee(__('By class'))
        ->assertSee($schoolClass->code);
});

test('admin reports dashboard shows at risk kpi', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.reports.dashboard'))
        ->assertOk()
        ->assertSee(__('Attendance at risk'));
});
