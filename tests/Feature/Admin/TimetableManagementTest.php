<?php

use App\Enums\DayOfWeek;
use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TimetableEntry;
use App\Models\User;

test('admin can create a timetable slot', function () {
    $admin = User::factory()->admin()->create();
    [$year, $schoolClass, $subject, $teacher] = timetableFixtures();

    $this->actingAs($admin)
        ->post(route('admin.timetables.store'), [
            'academic_year_id' => $year->id,
            'school_class_id' => $schoolClass->id,
            'day_of_week' => DayOfWeek::Monday->value,
            'period_number' => 1,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    expect(TimetableEntry::query()->count())->toBe(1);
});

test('admin cannot double-book a teacher in the same period', function () {
    $admin = User::factory()->admin()->create();
    [$year, $schoolClass, $subject, $teacher] = timetableFixtures();
    $otherClass = SchoolClass::factory()->create([
        'academic_year_id' => $year->id,
        'grade_id' => $schoolClass->grade_id,
    ]);
    $otherClass->subjects()->sync([$subject->id]);

    TimetableEntry::factory()->create([
        'academic_year_id' => $year->id,
        'school_class_id' => $schoolClass->id,
        'day_of_week' => DayOfWeek::Tuesday,
        'period_number' => 2,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.timetables.store'), [
            'academic_year_id' => $year->id,
            'school_class_id' => $otherClass->id,
            'day_of_week' => DayOfWeek::Tuesday->value,
            'period_number' => 2,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
        ])
        ->assertSessionHasErrors(['period_number']);
});

test('admin cannot place two lessons in the same class slot', function () {
    $admin = User::factory()->admin()->create();
    [$year, $schoolClass, $subject, $teacher] = timetableFixtures();
    $otherTeacher = Teacher::factory()->create();

    TimetableEntry::factory()->create([
        'academic_year_id' => $year->id,
        'school_class_id' => $schoolClass->id,
        'day_of_week' => DayOfWeek::Wednesday,
        'period_number' => 3,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.timetables.store'), [
            'academic_year_id' => $year->id,
            'school_class_id' => $schoolClass->id,
            'day_of_week' => DayOfWeek::Wednesday->value,
            'period_number' => 3,
            'subject_id' => $subject->id,
            'teacher_id' => $otherTeacher->id,
        ])
        ->assertSessionHasErrors(['period_number']);
});

test('teacher cannot manage timetables', function () {
    $teacher = User::factory()->teacher()->create();

    $this->actingAs($teacher)
        ->get(route('admin.timetables.index'))
        ->assertForbidden();
});

/**
 * @return array{0: AcademicYear, 1: SchoolClass, 2: Subject, 3: Teacher}
 */
function timetableFixtures(): array
{
    $year = AcademicYear::factory()->create();
    $grade = Grade::factory()->number(10)->create();
    $subject = Subject::factory()->forGradeRange(1, 13)->create();
    $schoolClass = SchoolClass::factory()->create([
        'academic_year_id' => $year->id,
        'grade_id' => $grade->id,
        'name' => 'A',
        'code' => '10-A',
    ]);
    $schoolClass->subjects()->sync([$subject->id]);
    $teacher = Teacher::factory()->create();

    return [$year, $schoolClass, $subject, $teacher];
}
