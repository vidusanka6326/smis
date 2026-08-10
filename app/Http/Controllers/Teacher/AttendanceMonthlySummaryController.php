<?php

namespace App\Http\Controllers\Teacher;

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

        $teacher = $request->user()->teacher;
        abort_unless($teacher !== null, 403);

        $month = $request->string('month')->toString() ?: now()->format('Y-m');
        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $end = (clone $start)->endOfMonth();

        $accessibleClassIds = $teacher->homeroomClasses()->pluck('id')
            ->merge($teacher->assignments()->pluck('school_class_id'))
            ->unique()
            ->filter();

        $schoolClassId = (int) $request->integer('school_class_id', 0);
        abort_if($schoolClassId > 0 && ! $accessibleClassIds->contains($schoolClassId), 403);

        $subjectId = $request->filled('subject_id') ? (int) $request->integer('subject_id') : null;
        $schoolClass = $schoolClassId > 0 ? SchoolClass::query()->findOrFail($schoolClassId) : null;

        if ($schoolClass !== null) {
            abort_unless($teacher->canViewStudentAttendance($schoolClass, $subjectId), 403);
        }

        $rows = $schoolClass !== null
            ? $summary->forClass($schoolClass->id, $start, $end, $subjectId)
            : [];

        return view('teacher.attendance.monthly', [
            'month' => $month,
            'schoolClasses' => SchoolClass::query()->whereIn('id', $accessibleClassIds)->orderBy('code')->get(),
            'selectedSchoolClassId' => $schoolClassId,
            'selectedSubjectId' => $subjectId,
            'rows' => $rows,
            'schoolClass' => $schoolClass,
        ]);
    }
}
