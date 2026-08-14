<?php

use App\Enums\DayOfWeek;
use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TimetableEntry;
use App\Models\User;
use App\Services\Agent\AgentToolRegistry;
use App\Services\Agent\Tools\AssignTimetableSlotTool;
use App\Services\Agent\Tools\FindFreePeriodsTool;
use App\Services\Agent\Tools\FindFreeTeachersTool;

function agentClassFixtures(): array
{
    $year = AcademicYear::factory()->current()->create();
    $grade = Grade::factory()->number(10)->create();
    $schoolClass = SchoolClass::factory()->create([
        'academic_year_id' => $year->id,
        'grade_id' => $grade->id,
        'code' => '10-A',
        'name' => 'A',
    ]);
    $subject = Subject::factory()->forGradeRange(1, 13)->create(['name' => 'Mathematics']);
    $schoolClass->subjects()->sync([$subject->id]);
    $busyTeacher = Teacher::factory()->create();
    $freeTeacher = Teacher::factory()->create();
    $freeTeacher->user->forceFill(['name' => 'Nimal Perera'])->save();

    TimetableEntry::factory()->create([
        'academic_year_id' => $year->id,
        'school_class_id' => $schoolClass->id,
        'subject_id' => $subject->id,
        'teacher_id' => $busyTeacher->id,
        'day_of_week' => DayOfWeek::Monday,
        'period_number' => 1,
    ]);

    return [$schoolClass, $subject, $busyTeacher, $freeTeacher];
}

test('admin can list free periods for a class', function () {
    [$schoolClass] = agentClassFixtures();
    $admin = User::factory()->admin()->create();

    $result = app(FindFreePeriodsTool::class)->handle($admin, ['class_code' => '10-A']);

    expect($result['ok'])->toBeTrue()
        ->and($result['class_code'])->toBe('10-A')
        ->and($result['count'])->toBeGreaterThan(0)
        ->and(collect($result['free_periods'])->contains(
            fn (array $slot): bool => $slot['day'] === 'Monday' && $slot['period'] === 1,
        ))->toBeFalse();
});

test('admin can list teachers free on a timeslot', function () {
    [, , $busyTeacher, $freeTeacher] = agentClassFixtures();
    $admin = User::factory()->admin()->create();

    $result = app(FindFreeTeachersTool::class)->handle($admin, [
        'day_of_week' => 'Monday',
        'period_number' => 1,
    ]);

    $ids = collect($result['teachers'])->pluck('id');

    expect($result['ok'])->toBeTrue()
        ->and($ids)->toContain($freeTeacher->id)
        ->and($ids)->not->toContain($busyTeacher->id);
});

test('admin can assign a teacher to a free period by name', function () {
    [$schoolClass, $subject, , $freeTeacher] = agentClassFixtures();
    $admin = User::factory()->admin()->create();

    $result = app(AssignTimetableSlotTool::class)->handle($admin, [
        'class_code' => '10-A',
        'day_of_week' => 'Monday',
        'period_number' => 3,
        'teacher_name' => 'Nimal Perera',
        'subject_name' => 'Mathematics',
    ]);

    expect($result['ok'])->toBeTrue()
        ->and($result['teacher'])->toBe('Nimal Perera');

    $this->assertDatabaseHas('timetables', [
        'school_class_id' => $schoolClass->id,
        'period_number' => 3,
        'teacher_id' => $freeTeacher->id,
        'subject_id' => $subject->id,
    ]);
});

test('officer can assign a timetable slot', function () {
    agentClassFixtures();
    $officer = User::factory()->officer()->create();

    $result = app(AssignTimetableSlotTool::class)->handle($officer, [
        'class_code' => '10A',
        'day_of_week' => 1,
        'period_number' => 2,
        'teacher_name' => 'Nimal Perera',
        'subject_name' => 'Mathematics',
    ]);

    expect($result['ok'])->toBeTrue();
});

test('teacher cannot assign a timetable slot through the registry', function () {
    agentClassFixtures();
    $teacherUser = User::factory()->teacher()->create();

    expect(app(AssignTimetableSlotTool::class)->authorized($teacherUser))->toBeFalse();

    $result = app(AgentToolRegistry::class)->execute($teacherUser, 'assign_timetable_slot', [
        'class_code' => '10-A',
        'day_of_week' => 'Monday',
        'period_number' => 3,
        'teacher_name' => 'Nimal Perera',
        'subject_name' => 'Mathematics',
    ]);

    expect($result['ok'])->toBeFalse();
});

test('teacher cannot list free teachers', function () {
    $teacherUser = User::factory()->teacher()->create();

    expect(app(FindFreeTeachersTool::class)->authorized($teacherUser))->toBeFalse();
});
