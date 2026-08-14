<?php

namespace App\Http\Controllers\Student;

use App\Enums\AttendanceStatus;
use App\Http\Controllers\Controller;
use App\Models\StudentAttendance;
use App\Services\Attendance\AttendanceMonthlySummary;
use App\Support\ListQuery;
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

        $filters = array_filter([
            'month' => $month,
            'scope' => $request->string('scope')->toString() ?: null,
            'status' => $request->string('status')->toString() ?: null,
        ], fn ($value) => filled($value));

        $records = $monthly['records'];

        if (($filters['scope'] ?? null) === 'class') {
            $records = $records->filter(fn ($record) => $record->attendanceSession?->isClassSession());
        } elseif (($filters['scope'] ?? null) === 'subject') {
            $records = $records->filter(fn ($record) => ! $record->attendanceSession?->isClassSession());
        }

        if (($filters['status'] ?? null) !== null) {
            $records = $records->filter(fn ($record) => $record->status->value === $filters['status']);
        }

        return view('student.attendance', [
            'student' => $student,
            'month' => $month,
            'percentage' => $monthly['percentage'],
            'counts' => $monthly['counts'],
            'records' => ListQuery::paginateCollection($records->values(), $request),
            'filters' => $filters,
            'statuses' => AttendanceStatus::cases(),
        ]);
    }
}
