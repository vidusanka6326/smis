<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSession;
use App\Models\SchoolClass;
use App\Services\Attendance\AttendanceMonthlySummary;
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

        return view('admin.attendance.monthly', [
            'month' => $month,
            'schoolClasses' => SchoolClass::query()->orderBy('code')->get(),
            'selectedSchoolClassId' => $schoolClassId,
            'selectedSubjectId' => $subjectId,
            'rows' => $rows,
            'schoolClass' => $schoolClassId > 0 ? SchoolClass::query()->with('subjects')->find($schoolClassId) : null,
        ]);
    }
}
