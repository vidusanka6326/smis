<?php

use App\Enums\AttendanceStatus;
use App\Enums\GradeLetter;
use App\Enums\TeacherAssignmentRole;
use App\Models\Exam;
use App\Models\ExamSubject;
use App\Models\Mark;
use App\Models\SchoolClass;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use App\Models\TeacherAttendance;
use App\Models\User;

test('admin enrollment report lists students and exports pdf', function () {
    $admin = User::factory()->admin()->create();
    [$year, $schoolClass, $subject, $student] = examFixtures(withStudent: true);

    $this->actingAs($admin)
        ->get(route('admin.reports.enrollment', ['school_class_id' => $schoolClass->id]))
        ->assertOk()
        ->assertSee($student->user->name)
        ->assertSee(__('Download PDF'));

    $pdf = $this->actingAs($admin)
        ->get(route('admin.reports.enrollment', [
            'school_class_id' => $schoolClass->id,
            'export' => 'pdf',
        ]));

    $pdf->assertOk();
    expect($pdf->headers->get('content-type'))->toStartWith('application/pdf');
});

test('admin staff attendance report lists teachers', function () {
    $admin = User::factory()->admin()->create();
    $teacher = Teacher::factory()->create();
    TeacherAttendance::factory()->create([
        'teacher_id' => $teacher->id,
        'date' => now()->toDateString(),
        'status' => AttendanceStatus::Present,
        'recorded_by' => $admin->id,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.reports.staff-attendance', ['month' => now()->format('Y-m')]))
        ->assertOk()
        ->assertSee($teacher->user->name);
});

test('admin exam results report lists marks', function () {
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
        'marks_obtained' => 81,
        'grade_letter' => GradeLetter::A,
        'is_pass' => true,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.reports.exam-results', ['exam_id' => $exam->id]))
        ->assertOk()
        ->assertSee($student->user->name)
        ->assertSee('81');
});

test('admin teacher assignment report lists assignments', function () {
    $admin = User::factory()->admin()->create();
    [$year, $schoolClass, $subject] = examFixtures();
    $teacher = Teacher::factory()->create();
    TeacherAssignment::factory()->create([
        'teacher_id' => $teacher->id,
        'school_class_id' => $schoolClass->id,
        'subject_id' => $subject->id,
        'academic_year_id' => $year->id,
        'role_in_assignment' => TeacherAssignmentRole::SubjectTeacher,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.reports.assignments', ['academic_year_id' => $year->id]))
        ->assertOk()
        ->assertSee($teacher->user->name)
        ->assertSee($schoolClass->code);
});

test('teacher cannot open another class enrollment report', function () {
    $user = User::factory()->teacher()->create();
    $teacher = Teacher::factory()->create(['user_id' => $user->id]);
    [$year, $ownClass] = examFixtures();
    $ownClass->update(['class_teacher_id' => $teacher->id]);
    TeacherAssignment::factory()->create([
        'teacher_id' => $teacher->id,
        'school_class_id' => $ownClass->id,
        'academic_year_id' => $year->id,
        'role_in_assignment' => TeacherAssignmentRole::ClassTeacher,
        'subject_id' => null,
    ]);

    $other = SchoolClass::factory()->create([
        'academic_year_id' => $year->id,
        'grade_id' => $ownClass->grade_id,
        'code' => '99-Z',
    ]);

    $this->actingAs($user)
        ->get(route('teacher.reports.enrollment', ['school_class_id' => $other->id]))
        ->assertForbidden();
});
