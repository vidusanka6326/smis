<?php

use App\Enums\DayOfWeek;
use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TimetableEntry;
use App\Models\User;

test('student can view their class timetable', function () {
    $user = User::factory()->student()->create();
    $year = AcademicYear::factory()->current()->create();
    $grade = Grade::factory()->number(5)->create();
    $subject = Subject::factory()->forGradeRange(1, 13)->create(['name' => 'Science Lab']);
    $schoolClass = SchoolClass::factory()->create([
        'academic_year_id' => $year->id,
        'grade_id' => $grade->id,
        'code' => '5-B',
    ]);
    $schoolClass->subjects()->sync([$subject->id]);
    Student::factory()->create([
        'user_id' => $user->id,
        'current_class_id' => $schoolClass->id,
    ]);
    $teacher = Teacher::factory()->create();

    TimetableEntry::factory()->create([
        'academic_year_id' => $year->id,
        'school_class_id' => $schoolClass->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'day_of_week' => DayOfWeek::Friday,
        'period_number' => 2,
    ]);

    $this->actingAs($user)
        ->get(route('student.timetable'))
        ->assertOk()
        ->assertSee('Science Lab');
});

test('student without a class cannot view timetable', function () {
    $user = User::factory()->student()->create();
    Student::factory()->create([
        'user_id' => $user->id,
        'current_class_id' => null,
    ]);

    $this->actingAs($user)
        ->get(route('student.timetable'))
        ->assertForbidden();
});
