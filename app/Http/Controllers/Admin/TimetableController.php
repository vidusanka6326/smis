<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Timetable\UpsertTimetableEntry;
use App\Enums\DayOfWeek;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTimetableEntryRequest;
use App\Http\Requests\Admin\UpdateTimetableEntryRequest;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TimetableEntry;
use App\Services\Timetable\TimetableConflictDetector;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TimetableController extends Controller
{
    public function index(Request $request, TimetableConflictDetector $detector): View
    {
        $this->authorize('viewAny', TimetableEntry::class);

        $academicYearId = (int) $request->integer(
            'academic_year_id',
            AcademicYear::query()->where('is_current', true)->value('id')
                ?? AcademicYear::query()->latest('starts_on')->value('id')
                ?? 0,
        );

        $schoolClassId = (int) $request->integer('school_class_id', 0);

        $entries = collect();
        $schoolClass = null;

        if ($academicYearId > 0 && $schoolClassId > 0) {
            $schoolClass = SchoolClass::query()
                ->with(['subjects', 'grade'])
                ->findOrFail($schoolClassId);
            $entries = $detector->entriesForClass($schoolClassId, $academicYearId);
        }

        $grid = [];
        foreach (DayOfWeek::schoolDays() as $day) {
            foreach (range(1, TimetableEntry::MAX_PERIODS) as $period) {
                $grid[$day->value][$period] = $entries->first(
                    fn (TimetableEntry $entry): bool => $entry->day_of_week === $day
                        && $entry->period_number === $period,
                );
            }
        }

        return view('admin.timetables.index', [
            'academicYears' => AcademicYear::query()->orderByDesc('starts_on')->get(),
            'schoolClasses' => SchoolClass::query()
                ->when($academicYearId > 0, fn ($q) => $q->where('academic_year_id', $academicYearId))
                ->orderBy('code')
                ->get(),
            'selectedAcademicYearId' => $academicYearId,
            'selectedSchoolClassId' => $schoolClassId,
            'schoolClass' => $schoolClass,
            'grid' => $grid,
            'days' => DayOfWeek::schoolDays(),
            'periods' => range(1, TimetableEntry::MAX_PERIODS),
            'subjects' => Subject::query()->orderBy('name')->get(),
            'teachers' => Teacher::query()->with('user')->orderBy('employee_no')->get(),
        ]);
    }

    public function store(StoreTimetableEntryRequest $request, UpsertTimetableEntry $upsert): RedirectResponse
    {
        $entry = $upsert->handle($request->validated());

        return redirect()
            ->route('admin.timetables.index', [
                'academic_year_id' => $entry->academic_year_id,
                'school_class_id' => $entry->school_class_id,
            ])
            ->with('status', __('Timetable slot saved.'));
    }

    public function edit(TimetableEntry $timetableEntry): View
    {
        $this->authorize('update', $timetableEntry);

        return view('admin.timetables.edit', [
            'entry' => $timetableEntry->load(['schoolClass', 'subject', 'teacher.user']),
            'academicYears' => AcademicYear::query()->orderByDesc('starts_on')->get(),
            'schoolClasses' => SchoolClass::query()->orderBy('code')->get(),
            'subjects' => Subject::query()->orderBy('name')->get(),
            'teachers' => Teacher::query()->with('user')->orderBy('employee_no')->get(),
            'days' => DayOfWeek::schoolDays(),
            'periods' => range(1, TimetableEntry::MAX_PERIODS),
        ]);
    }

    public function update(
        UpdateTimetableEntryRequest $request,
        TimetableEntry $timetableEntry,
        UpsertTimetableEntry $upsert,
    ): RedirectResponse {
        $entry = $upsert->handle($request->validated(), $timetableEntry);

        return redirect()
            ->route('admin.timetables.index', [
                'academic_year_id' => $entry->academic_year_id,
                'school_class_id' => $entry->school_class_id,
            ])
            ->with('status', __('Timetable slot updated.'));
    }

    public function destroy(TimetableEntry $timetableEntry): RedirectResponse
    {
        $this->authorize('delete', $timetableEntry);

        $yearId = $timetableEntry->academic_year_id;
        $classId = $timetableEntry->school_class_id;
        $timetableEntry->delete();

        return redirect()
            ->route('admin.timetables.index', [
                'academic_year_id' => $yearId,
                'school_class_id' => $classId,
            ])
            ->with('status', __('Timetable slot deleted.'));
    }
}
