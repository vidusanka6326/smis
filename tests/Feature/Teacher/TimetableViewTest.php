<?php

use App\Enums\DayOfWeek;
use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TimetableEntry;
use App\Models\User;

test('teacher can view own timetable', function () {
    $user = User::factory()->teacher()->create();
    $teacher = Teacher::factory()->create(['user_id' => $user->id]);
    $year = AcademicYear::factory()->current()->create();
    $grade = Grade::factory()->number(6)->create();
    $subject = Subject::factory()->forGradeRange(1, 13)->create();
    $schoolClass = SchoolClass::factory()->create([
        'academic_year_id' => $year->id,
        'grade_id' => $grade->id,
    ]);
    $schoolClass->subjects()->sync([$subject->id]);

    TimetableEntry::factory()->create([
        'academic_year_id' => $year->id,
        'school_class_id' => $schoolClass->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'day_of_week' => DayOfWeek::Thursday,
        'period_number' => 4,
    ]);

    $this->actingAs($user)
        ->get(route('teacher.timetable', ['academic_year_id' => $year->id]))
        ->assertOk()
        ->assertSee(__('My timetable'))
        ->assertSee($schoolClass->code);
});

test('student cannot open teacher timetable', function () {
    $student = User::factory()->student()->create();

    $this->actingAs($student)
        ->get(route('teacher.timetable'))
        ->assertForbidden();
});
