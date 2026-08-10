<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Mark;
use App\Models\Report;
use App\Models\StudentAttendance;
use App\Services\Attendance\AttendancePercentageCalculator;
use App\Services\Reporting\ReportCsvExporter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OwnReportController extends Controller
{
    public function __invoke(
        Request $request,
        AttendancePercentageCalculator $attendanceCalculator,
        ReportCsvExporter $csv,
    ): View|StreamedResponse {
        $this->authorize('viewOwn', Report::class);

        $student = $request->user()->student;
        abort_unless($student !== null, 403);

        $month = $request->string('month')->toString() ?: now()->format('Y-m');
        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $end = (clone $start)->endOfMonth();

        $attendanceRecords = StudentAttendance::query()
            ->with('attendanceSession')
            ->where('student_id', $student->id)
            ->whereHas('attendanceSession', function ($q) use ($start, $end): void {
                $q->whereDate('date', '>=', $start->toDateString())
                    ->whereDate('date', '<=', $end->toDateString());
            })
            ->get();

        $attendancePercentage = $attendanceCalculator->percentage($attendanceRecords->pluck('status')->all());

        $marks = Mark::query()
            ->with(['examSubject.exam', 'examSubject.subject'])
            ->where('student_id', $student->id)
            ->whereHas('examSubject.exam', fn ($q) => $q->whereNotNull('published_at'))
            ->get();

        if ($request->string('export')->toString() === 'csv') {
            $rows = $marks->map(fn (Mark $mark): array => [
                $mark->examSubject?->exam?->name,
                $mark->examSubject?->subject?->name,
                $mark->marks_obtained,
                $mark->grade_letter->value,
                $mark->is_pass ? 'pass' : 'fail',
            ]);

            return $csv->download('my-results.csv', [__('Exam'), __('Subject'), __('Marks'), __('Grade'), __('Result')], $rows);
        }

        return view('student.report', [
            'student' => $student->load('currentClass.grade'),
            'month' => $month,
            'attendancePercentage' => $attendancePercentage,
            'attendanceRecords' => $attendanceRecords,
            'marks' => $marks,
            'print' => $request->boolean('print'),
        ]);
    }
}
