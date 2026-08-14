<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Concerns\RespondsWithReportExport;
use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\SchoolClass;
use App\Services\Reporting\AttendanceAnalyticsReport;
use App\Services\Reporting\ReportCsvExporter;
use App\Services\Reporting\ReportPdfExporter;
use App\Services\Reporting\TeacherReportScope;
use App\Support\ListQuery;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class AtRiskAttendanceReportController extends Controller
{
    use RespondsWithReportExport;

    public function __invoke(
        Request $request,
        TeacherReportScope $scope,
        AttendanceAnalyticsReport $report,
        ReportCsvExporter $csv,
        ReportPdfExporter $pdf,
    ): View|Response {
        $this->authorize('viewAny', Report::class);

        $teacher = $request->user()->teacher;
        abort_unless($teacher !== null, 403);

        [$month, $start, $end] = $this->monthRange($request);
        $classIds = $scope->accessibleClassIds($teacher);
        $selectedClassId = $request->filled('school_class_id') ? $request->integer('school_class_id') : null;

        if ($selectedClassId !== null && ! in_array($selectedClassId, $classIds, true)) {
            abort(403);
        }

        $data = $report->forMonth(
            $start,
            $end,
            $selectedClassId !== null ? [$selectedClassId] : $classIds,
        );

        $headers = [__('Student'), __('Class'), __('%'), __('Present'), __('Absent'), __('Late'), __('Excused')];
        $rows = collect($data['at_risk'])->map(fn (array $row): array => [
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
            "teacher-attendance-at-risk-{$month}",
            $headers,
            $rows,
            __('Attendance at risk'),
            [['title' => __('Below :pct%', ['pct' => (int) $data['summary']['threshold']]), 'headers' => $headers, 'rows' => $rows->all()]],
            $month,
        );

        if ($exported !== null) {
            return $exported;
        }

        return view('reports.at-risk', [
            'data' => $data,
            'rows' => ListQuery::paginateCollection($data['at_risk'], $request),
            'month' => $month,
            'filters' => array_filter(['month' => $month, 'school_class_id' => $selectedClassId], fn ($value) => filled($value)),
            'schoolClasses' => SchoolClass::query()->whereIn('id', $classIds)->orderBy('code')->get(),
            'selectedSchoolClassId' => $selectedClassId,
            'action' => route('teacher.reports.at-risk'),
            'catalogRoute' => 'teacher.reports.dashboard',
            'exportQuery' => ['month' => $month, 'school_class_id' => $selectedClassId],
        ]);
    }
}
