<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\StudentAttendance;
use App\Services\Attendance\AttendanceMonthlySummary;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function __invoke(Request $request, AttendanceMonthlySummary $summary): View
    {
        $this->authorize('viewAny', StudentAttendance::class);

        $student = $request->user()->student;
        abort_unless($student !== null, 403);

        $month = $request->string('month')->toString() ?: now()->format('Y-m');
        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $end = (clone $start)->endOfMonth();

        $monthly = $summary->forStudent($student, $start, $end);

        return view('student.attendance', [
            'student' => $student,
            'month' => $month,
            'percentage' => $monthly['percentage'],
            'counts' => $monthly['counts'],
            'records' => $monthly['records'],
        ]);
    }
}
