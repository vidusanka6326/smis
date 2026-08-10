<?php

use App\Enums\DayOfWeek;
use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\ReliefTeacherAssignment;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TimetableEntry;
use App\Models\User;
use Illuminate\Support\Carbon;

test('admin can assign a relief teacher on a matching weekday', function () {
    $admin = User::factory()->admin()->create();
    [$entry, $relief] = reliefFixtures();

    $date = Carbon::now()->startOfWeek(Carbon::MONDAY)
        ->addDays($entry->day_of_week->value - 1);
    if ($date->isPast()) {
        $date = $date->addWeek();
    }

    $this->actingAs($admin)
        ->post(route('admin.relief-assignments.store'), [
            'timetable_entry_id' => $entry->id,
            'relief_teacher_id' => $relief->id,
            'date' => $date->toDateString(),
            'reason' => 'Medical leave',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.relief-assignments.index'));

    expect(ReliefTeacherAssignment::query()->count())->toBe(1);
});

test('relief date must match timetable weekday', function () {
    $admin = User::factory()->admin()->create();
    [$entry, $relief] = reliefFixtures(DayOfWeek::Monday);

    $tuesday = Carbon::now()->startOfWeek(Carbon::MONDAY)->addDay();
    if ($tuesday->isPast()) {
        $tuesday = $tuesday->addWeek();
    }

    $this->actingAs($admin)
        ->post(route('admin.relief-assignments.store'), [
            'timetable_entry_id' => $entry->id,
            'relief_teacher_id' => $relief->id,
            'date' => $tuesday->toDateString(),
        ])
        ->assertSessionHasErrors(['date']);
});

test('relief teacher cannot be the original teacher', function () {
    $admin = User::factory()->admin()->create();
    [$entry] = reliefFixtures();

    $date = Carbon::now()->startOfWeek(Carbon::MONDAY)
        ->addDays($entry->day_of_week->value - 1);
    if ($date->isPast()) {
        $date = $date->addWeek();
    }

    $this->actingAs($admin)
        ->post(route('admin.relief-assignments.store'), [
            'timetable_entry_id' => $entry->id,
            'relief_teacher_id' => $entry->teacher_id,
            'date' => $date->toDateString(),
        ])
        ->assertSessionHasErrors(['relief_teacher_id']);
});

/**
 * @return array{0: TimetableEntry, 1: Teacher}
 */
function reliefFixtures(?DayOfWeek $day = null): array
{
    $year = AcademicYear::factory()->create();
    $grade = Grade::factory()->number(9)->create();
    $subject = Subject::factory()->forGradeRange(1, 13)->create();
    $schoolClass = SchoolClass::factory()->create([
        'academic_year_id' => $year->id,
        'grade_id' => $grade->id,
    ]);
    $schoolClass->subjects()->sync([$subject->id]);
    $teacher = Teacher::factory()->create();
    $relief = Teacher::factory()->create();

    $entry = TimetableEntry::factory()->create([
        'academic_year_id' => $year->id,
        'school_class_id' => $schoolClass->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'day_of_week' => $day ?? DayOfWeek::Monday,
        'period_number' => 1,
    ]);

    return [$entry, $relief];
}
