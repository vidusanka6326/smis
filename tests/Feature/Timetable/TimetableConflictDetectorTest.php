<?php

use App\Enums\DayOfWeek;
use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TimetableEntry;
use App\Services\Timetable\TimetableConflictDetector;

test('detector reports class and teacher slot conflicts', function () {
    $year = AcademicYear::factory()->create();
    $grade = Grade::factory()->number(4)->create();
    $subject = Subject::factory()->forGradeRange(1, 13)->create();
    $classA = SchoolClass::factory()->create(['academic_year_id' => $year->id, 'grade_id' => $grade->id]);
    $classB = SchoolClass::factory()->create(['academic_year_id' => $year->id, 'grade_id' => $grade->id]);
    $classA->subjects()->sync([$subject->id]);
    $classB->subjects()->sync([$subject->id]);
    $teacher = Teacher::factory()->create();

    TimetableEntry::factory()->create([
        'academic_year_id' => $year->id,
        'school_class_id' => $classA->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'day_of_week' => DayOfWeek::Monday,
        'period_number' => 1,
    ]);

    $detector = app(TimetableConflictDetector::class);

    $sameClass = $detector->detect($year->id, $classA->id, DayOfWeek::Monday, 1, Teacher::factory()->create()->id);
    $sameTeacher = $detector->detect($year->id, $classB->id, DayOfWeek::Monday, 1, $teacher->id);
    $clear = $detector->detect($year->id, $classB->id, DayOfWeek::Monday, 2, $teacher->id);

    expect($sameClass['class_slot'])->toBeTrue()
        ->and($sameClass['teacher_slot'])->toBeFalse()
        ->and($sameTeacher['teacher_slot'])->toBeTrue()
        ->and($clear['class_slot'])->toBeFalse()
        ->and($clear['teacher_slot'])->toBeFalse();
});
