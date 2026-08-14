<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\RespondsWithReportExport;
use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\SchoolClass;
use App\Services\Reporting\AttendanceAnalyticsReport;
use App\Services\Reporting\ReportCsvExporter;
use App\Services\Reporting\ReportPdfExporter;
use App\Support\ListQuery;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class AttendanceReportController extends Controller
{
    use RespondsWithReportExport;

    public function __invoke(
        Request $request,
        AttendanceAnalyticsReport $report,
        ReportCsvExporter $csv,
        ReportPdfExporter $pdf,
    ): View|Response {
        $this->authorize('viewAny', Report::class);

        [$month, $start, $end] = $this->monthRange($request);
        $classId = $request->filled('school_class_id') ? $request->integer('school_class_id') : null;
        $data = $report->forMonth($start, $end, $classId !== null ? [$classId] : null);

        $headers = [__('Student'), __('Class'), __('%'), __('Present'), __('Absent'), __('Late'), __('Excused')];
        $rows = collect($data['student_rows'])->map(fn (array $row): array => [
            $row['name'],
            $row['class'],
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
            "attendance-{$month}",
            $headers,
            $rows,
            __('Student attendance'),
            [
                [
                    'title' => __('All students'),
                    'headers' => $headers,
                    'rows' => $rows->all(),
                ],
            ],
            $month,
            'landscape',
        );

        if ($exported !== null) {
            return $exported;
        }

        return view('reports.attendance', [
            'data' => $data,
            'studentRows' => ListQuery::paginateCollection($data['student_rows'], $request),
            'month' => $month,
            'filters' => array_filter([
                'month' => $month,
                'school_class_id' => $classId,
            ], fn ($value) => filled($value)),
            'schoolClasses' => SchoolClass::query()->orderBy('code')->get(),
            'selectedSchoolClassId' => $classId,
            'action' => route('admin.reports.attendance'),
            'catalogRoute' => 'admin.reports.dashboard',
            'exportQuery' => ['month' => $month, 'school_class_id' => $classId],
        ]);
    }
}
