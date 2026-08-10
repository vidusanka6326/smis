<?php

use App\Enums\ActivityAction;
use App\Enums\AttendanceStatus;
use App\Enums\RoleName;
use App\Enums\UserStatus;
use App\Models\AcademicYear;
use App\Models\ActivityLog;
use App\Models\AttendanceSession;
use App\Models\Exam;
use App\Models\ExamSubject;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;

test('admin can view the activity log index', function () {
    $admin = User::factory()->admin()->create();

    ActivityLog::factory()->create([
        'causer_id' => $admin->id,
        'action' => ActivityAction::UserCreated,
        'description' => 'Created demo user.',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.activity-logs.index'))
        ->assertOk()
        ->assertSee(__('Activity log'))
        ->assertSee('Created demo user.');
});

test('admin can filter activity logs by action', function () {
    $admin = User::factory()->admin()->create();

    ActivityLog::factory()->create([
        'causer_id' => $admin->id,
        'action' => ActivityAction::UserCreated,
        'description' => 'User creation entry.',
    ]);
    ActivityLog::factory()->create([
        'causer_id' => $admin->id,
        'action' => ActivityAction::MarksUpserted,
        'description' => 'Marks entry.',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.activity-logs.index', ['action' => ActivityAction::MarksUpserted->value]))
        ->assertOk()
        ->assertSee('Marks entry.')
        ->assertDontSee('User creation entry.');
});

test('teacher cannot view activity logs', function () {
    $teacher = User::factory()->teacher()->create();

    $this->actingAs($teacher)
        ->get(route('admin.activity-logs.index'))
        ->assertForbidden();
});

test('creating a user writes an activity log', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post(route('admin.users.store'), [
        'name' => 'Audited Teacher',
        'email' => 'audited.teacher@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => RoleName::Teacher->value,
        'status' => UserStatus::Active->value,
    ])->assertRedirect(route('admin.dashboard'));

    $log = ActivityLog::query()->where('action', ActivityAction::UserCreated)->first();

    expect($log)->not->toBeNull()
        ->and($log->causer_id)->toBe($admin->id)
        ->and($log->properties['role'] ?? null)->toBe(RoleName::Teacher->value);
});

test('mark entry writes an activity log', function () {
    $admin = User::factory()->admin()->create();
    [$year, $schoolClass, $subject, $student] = examFixtures(withStudent: true);

    $exam = Exam::factory()->create([
        'academic_year_id' => $year->id,
        'grade_id' => $schoolClass->grade_id,
        'school_class_id' => $schoolClass->id,
    ]);
    $examSubject = ExamSubject::factory()->create([
        'exam_id' => $exam->id,
        'subject_id' => $subject->id,
    ]);

    $this->actingAs($admin)
        ->put(route('admin.marks.update', $examSubject), [
            'records' => [
                [
                    'student_id' => $student->id,
                    'marks_obtained' => 72,
                ],
            ],
        ])
        ->assertSessionHasNoErrors();

    expect(ActivityLog::query()->where('action', ActivityAction::MarksUpserted)->exists())->toBeTrue();
});

test('publishing an exam writes an activity log', function () {
    $admin = User::factory()->admin()->create();
    [$year, $schoolClass, $subject] = examFixtures();

    $exam = Exam::factory()->create([
        'academic_year_id' => $year->id,
        'grade_id' => $schoolClass->grade_id,
        'school_class_id' => $schoolClass->id,
    ]);
    ExamSubject::factory()->create([
        'exam_id' => $exam->id,
        'subject_id' => $subject->id,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.exams.publish', $exam))
        ->assertRedirect();

    expect(ActivityLog::query()->where('action', ActivityAction::ExamPublished)->exists())->toBeTrue();
});

test('admin edit of finalized attendance is audited as post-finalization', function () {
    $admin = User::factory()->admin()->create();
    [$year, $schoolClass, $student] = attendanceFixturesForAudit();

    $session = AttendanceSession::factory()->forClass($schoolClass)->create([
        'date' => now()->toDateString(),
        'finalized_at' => now(),
    ]);

    $this->actingAs($admin)
        ->put(route('admin.attendance.sessions.update', $session), [
            'academic_year_id' => $year->id,
            'school_class_id' => $schoolClass->id,
            'date' => now()->toDateString(),
            'records' => [
                [
                    'student_id' => $student->id,
                    'status' => AttendanceStatus::Absent->value,
                ],
            ],
        ])
        ->assertSessionHasNoErrors();

    $log = ActivityLog::query()->where('action', ActivityAction::AttendanceSessionUpserted)->latest('id')->first();

    expect($log)->not->toBeNull()
        ->and($log->properties['post_finalization_edit'] ?? false)->toBeTrue();
});

/**
 * @return array{0: AcademicYear, 1: SchoolClass, 2: Student}
 */
function attendanceFixturesForAudit(): array
{
    $year = AcademicYear::factory()->current()->create();
    $grade = Grade::factory()->number(8)->create();
    $schoolClass = SchoolClass::factory()->create([
        'academic_year_id' => $year->id,
        'grade_id' => $grade->id,
    ]);
    $student = Student::factory()->create([
        'current_class_id' => $schoolClass->id,
    ]);

    return [$year, $schoolClass, $student];
}
