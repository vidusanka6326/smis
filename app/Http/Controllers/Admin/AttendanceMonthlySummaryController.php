<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSession;
use App\Models\SchoolClass;
use App\Services\Attendance\AttendanceMonthlySummary;
use App\Support\ListQuery;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceMonthlySummaryController extends Controller
{
    public function __invoke(Request $request, AttendanceMonthlySummary $summary): View
    {
        $this->authorize('viewAny', AttendanceSession::class);

        $month = $request->string('month')->toString() ?: now()->format('Y-m');
        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $end = (clone $start)->endOfMonth();
        $schoolClassId = (int) $request->integer('school_class_id', 0);
        $subjectId = $request->filled('subject_id') ? (int) $request->integer('subject_id') : null;

        $rows = $schoolClassId > 0
            ? $summary->forClass($schoolClassId, $start, $end, $subjectId)
            : [];

        $filters = array_filter([
            'month' => $month,
            'school_class_id' => $schoolClassId > 0 ? (string) $schoolClassId : null,
            'subject_id' => $subjectId,
        ], fn ($value) => filled($value));

        return view('admin.attendance.monthly', [
            'month' => $month,
            'schoolClasses' => SchoolClass::query()->orderBy('code')->get(),
            'selectedSchoolClassId' => $schoolClassId,
            'selectedSubjectId' => $subjectId,
            'rows' => ListQuery::paginateCollection($rows, $request),
            'schoolClass' => $schoolClassId > 0 ? SchoolClass::query()->with('subjects')->find($schoolClassId) : null,
            'filters' => $filters,
        ]);
    }
}
