<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\RespondsWithReportExport;
use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\Teacher;
use App\Services\Reporting\ReportCsvExporter;
use App\Services\Reporting\ReportPdfExporter;
use App\Services\Reporting\StaffAttendanceReport;
use App\Support\ListQuery;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class StaffAttendanceReportController extends Controller
{
    use RespondsWithReportExport;

    public function __invoke(
        Request $request,
        StaffAttendanceReport $report,
        ReportCsvExporter $csv,
        ReportPdfExporter $pdf,
    ): View|Response {
        $this->authorize('viewAny', Report::class);

        [$month, $start, $end] = $this->monthRange($request);
        $teacherId = $request->filled('teacher_id') ? $request->integer('teacher_id') : null;
        $staffRows = $report->forMonth($start, $end, $teacherId);

        $headers = [__('Teacher'), __('Employee no.'), __('%'), __('Present'), __('Absent'), __('Late'), __('Excused')];
        $rows = collect($staffRows)->map(fn (array $row): array => [
            $row['name'],
            $row['employee_no'],
            $row['percentage'],
            $row['present'],
            $row['absent'],
            $row['late'],
            $row['excused'],
        ]);

        $exported = $this->exportIfRequested(
            $request,
            $csv,
            $pdf,
            "teacher-attendance-{$month}",
            $headers,
            $rows,
            __('Teacher attendance'),
            [['title' => __('Staff'), 'headers' => $headers, 'rows' => $rows->all()]],
            $month,
            'landscape',
        );

        if ($exported !== null) {
            return $exported;
        }

        return view('reports.staff-attendance', [
            'staffRows' => ListQuery::paginateCollection($staffRows, $request),
            'month' => $month,
            'filters' => array_filter(['month' => $month, 'teacher_id' => $teacherId], fn ($value) => filled($value)),
            'teachers' => Teacher::query()->with('user')->orderBy('id')->get(),
            'selectedTeacherId' => $teacherId,
            'action' => route('admin.reports.staff-attendance'),
            'catalogRoute' => 'admin.reports.dashboard',
            'exportQuery' => ['month' => $month, 'teacher_id' => $teacherId],
        ]);
    }
}
