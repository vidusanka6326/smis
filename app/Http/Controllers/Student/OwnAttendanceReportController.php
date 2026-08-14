<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Concerns\RespondsWithReportExport;
use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Services\Reporting\ReportCsvExporter;
use App\Services\Reporting\ReportPdfExporter;
use App\Services\Reporting\StudentOwnReport;
use App\Support\ListQuery;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class OwnAttendanceReportController extends Controller
{
    use RespondsWithReportExport;

    public function __invoke(
        Request $request,
        StudentOwnReport $ownReport,
        ReportCsvExporter $csv,
        ReportPdfExporter $pdf,
    ): View|Response {
        $this->authorize('viewOwn', Report::class);

        $student = $request->user()->student;
        abort_unless($student !== null, 403);

        [$month, $start, $end] = $this->monthRange($request);
        $attendance = $ownReport->attendanceForMonth($student, $start, $end);
        $detailRows = $ownReport->attendanceRows($attendance['records']);

        $headers = [__('Date'), __('Scope'), __('Subject'), __('Status')];
        $rows = collect($detailRows)->map(fn (array $row): array => [
            $row['date'],
            $row['scope'],
            $row['subject'],
            $row['status'],
        ]);

        $exported = $this->exportIfRequested(
            $request,
            $csv,
            $pdf,
            "my-attendance-{$month}",
            $headers,
            $rows,
            __('My attendance'),
            [['title' => $month, 'headers' => $headers, 'rows' => $rows->all()]],
            $student->user?->name,
        );

        if ($exported !== null) {
            return $exported;
        }

        return view('student.reports.attendance', [
            'student' => $student->load(['currentClass', 'user']),
            'month' => $month,
            'percentage' => $attendance['percentage'],
            'counts' => $attendance['counts'],
            'rows' => ListQuery::paginateCollection($detailRows, $request),
            'filters' => array_filter(['month' => $month], fn ($value) => filled($value)),
            'action' => route('student.reports.attendance'),
            'catalogRoute' => 'student.reports',
            'exportQuery' => ['month' => $month],
        ]);
    }
}
