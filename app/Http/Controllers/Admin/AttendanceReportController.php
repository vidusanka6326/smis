<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Services\Reporting\AttendanceAnalyticsReport;
use App\Services\Reporting\ReportCsvExporter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceReportController extends Controller
{
    public function __invoke(Request $request, AttendanceAnalyticsReport $report, ReportCsvExporter $csv): View|StreamedResponse
    {
        $this->authorize('viewAny', Report::class);

        $month = $request->string('month')->toString() ?: now()->format('Y-m');
        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $end = (clone $start)->endOfMonth();
        $data = $report->forMonth($start, $end);

        if ($request->string('export')->toString() === 'csv') {
            $rows = collect($data['student_rows'])->map(fn (array $row): array => [
                $row['name'],
                $row['class'],
                $row['percentage'],
                $row['present'],
                $row['absent'],
                $row['late'],
                $row['excused'],
            ]);

            return $csv->download(
                "attendance-{$month}.csv",
                [__('Student'), __('Class'), __('%'), __('Present'), __('Absent'), __('Late'), __('Excused')],
                $rows,
            );
        }

        return view('admin.reports.attendance', [
            'data' => $data,
            'month' => $month,
            'print' => $request->boolean('print'),
        ]);
    }
}
