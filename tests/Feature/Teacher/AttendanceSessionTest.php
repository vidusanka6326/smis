<?php

use App\Enums\AttendanceStatus;
use App\Enums\TeacherAssignmentRole;
use App\Models\AcademicYear;
use App\Models\AttendanceSession;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use App\Models\User;

test('class teacher can take attendance for own class', function () {
    [$user, $teacher, $year, $schoolClass, $student] = classTeacherAttendanceFixtures();

    $this->actingAs($user)
        ->post(route('teacher.attendance.sessions.store'), [
            'academic_year_id' => $year->id,
            'school_class_id' => $schoolClass->id,
            'date' => now()->toDateString(),
            'records' => [
                [
                    'student_id' => $student->id,
                    'status' => AttendanceStatus::Late->value,
                ],
            ],
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    expect(AttendanceSession::query()->where('taken_by_teacher_id', $teacher->id)->count())->toBe(1);
});

test('subject teacher can take attendance for assigned subject', function () {
    $user = User::factory()->teacher()->create();
    $teacher = Teacher::factory()->create(['user_id' => $user->id]);
    $year = AcademicYear::factory()->current()->create();
    $grade = Grade::factory()->number(9)->create();
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

    $this->actingAs($user)
        ->post(route('teacher.attendance.sessions.store'), [
            'academic_year_id' => $year->id,
            'school_class_id' => $schoolClass->id,
            'subject_id' => $subject->id,
            'date' => now()->toDateString(),
            'records' => [
                [
                    'student_id' => $student->id,
                    'status' => AttendanceStatus::Present->value,
                ],
            ],
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect();
});

test('subject teacher cannot take class-level attendance for unassigned homeroom', function () {
    $user = User::factory()->teacher()->create();
    $teacher = Teacher::factory()->create(['user_id' => $user->id]);
    $year = AcademicYear::factory()->current()->create();
    $grade = Grade::factory()->number(7)->create();
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

    $this->actingAs($user)
        ->post(route('teacher.attendance.sessions.store'), [
            'academic_year_id' => $year->id,
            'school_class_id' => $schoolClass->id,
            'date' => now()->toDateString(),
            'records' => [
                [
                    'student_id' => $student->id,
                    'status' => AttendanceStatus::Present->value,
                ],
            ],
        ])
        ->assertForbidden();
});

test('class teacher attendance roster uses flux status selects', function () {
    [$user, $teacher, $year, $schoolClass, $student] = classTeacherAttendanceFixtures();

    $this->actingAs($user)
        ->get(route('teacher.attendance.sessions.create', [
            'academic_year_id' => $year->id,
            'school_class_id' => $schoolClass->id,
        ]))
        ->assertOk()
        ->assertSee($student->user->name)
        ->assertSee('data-flux-select-native', false)
        ->assertSee(AttendanceStatus::Present->label())
        ->assertSee('name="records[0][status]"', false)
        ->assertDontSee('<select name="records[0][status]" class="rounded border', false)
        ->assertSee('name="finalize"', false)
        ->assertSee('data-flux-checkbox', false);
});

test('teacher cannot edit finalized attendance session', function () {
    [$user, $teacher, $year, $schoolClass, $student] = classTeacherAttendanceFixtures();

    $session = AttendanceSession::factory()->forClass($schoolClass)->finalized()->create([
        'taken_by_teacher_id' => $teacher->id,
        'date' => now()->subDay()->toDateString(),
    ]);

    $this->actingAs($user)
        ->put(route('teacher.attendance.sessions.update', $session), [
            'academic_year_id' => $year->id,
            'school_class_id' => $schoolClass->id,
            'date' => $session->date->toDateString(),
            'records' => [
                [
                    'student_id' => $student->id,
                    'status' => AttendanceStatus::Absent->value,
                ],
            ],
        ])
        ->assertForbidden();
});

/**
 * @return array{0: User, 1: Teacher, 2: AcademicYear, 3: SchoolClass, 4: Student}
 */
function classTeacherAttendanceFixtures(): array
{
    $user = User::factory()->teacher()->create();
    $teacher = Teacher::factory()->create(['user_id' => $user->id]);
    $year = AcademicYear::factory()->current()->create();
    $grade = Grade::factory()->number(5)->create();
    $schoolClass = SchoolClass::factory()->create([
        'academic_year_id' => $year->id,
        'grade_id' => $grade->id,
        'class_teacher_id' => $teacher->id,
    ]);
    $student = Student::factory()->create(['current_class_id' => $schoolClass->id]);

    TeacherAssignment::factory()->create([
        'teacher_id' => $teacher->id,
        'school_class_id' => $schoolClass->id,
        'subject_id' => null,
        'academic_year_id' => $year->id,
        'role_in_assignment' => TeacherAssignmentRole::ClassTeacher,
    ]);

    return [$user, $teacher, $year, $schoolClass, $student];
}
