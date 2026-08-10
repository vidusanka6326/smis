<?php

namespace App\Http\Controllers\Student;

use App\Enums\DayOfWeek;
use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\TimetableEntry;
use App\Services\Timetable\TimetableConflictDetector;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TimetableController extends Controller
{
    public function __invoke(Request $request, TimetableConflictDetector $detector): View
    {
        $this->authorize('viewAny', TimetableEntry::class);

        $student = $request->user()->student;
        abort_if($student === null || $student->current_class_id === null, 403);

        $academicYearId = (int) ($student->currentClass?->academic_year_id
            ?? AcademicYear::query()->where('is_current', true)->value('id')
            ?? 0);

        $entries = $academicYearId > 0
            ? $detector->entriesForClass($student->current_class_id, $academicYearId)
            : collect();

        $grid = [];
        foreach (DayOfWeek::schoolDays() as $day) {
            foreach (range(1, TimetableEntry::MAX_PERIODS) as $period) {
                $grid[$day->value][$period] = $entries->first(
                    fn (TimetableEntry $entry): bool => $entry->day_of_week === $day
                        && $entry->period_number === $period,
                );
            }
        }

        return view('student.timetable', [
            'student' => $student->load('currentClass'),
            'grid' => $grid,
            'days' => DayOfWeek::schoolDays(),
            'periods' => range(1, TimetableEntry::MAX_PERIODS),
        ]);
    }
}
