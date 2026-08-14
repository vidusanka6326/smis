<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\SchoolClass;
use App\Services\Reporting\AttendanceAnalyticsReport;
use App\Services\Reporting\ReportCsvExporter;
use App\Services\Reporting\TeacherReportScope;
use App\Support\ListQuery;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceReportController extends Controller
{
    public function __invoke(
        Request $request,
        TeacherReportScope $scope,
        AttendanceAnalyticsReport $report,
        ReportCsvExporter $csv,
    ): View|StreamedResponse {
        $this->authorize('viewAny', Report::class);

        $teacher = $request->user()->teacher;
        abort_unless($teacher !== null, 403);

        $month = $request->string('month')->toString() ?: now()->format('Y-m');
        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $end = (clone $start)->endOfMonth();
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
                "teacher-attendance-{$month}.csv",
                [__('Student'), __('Class'), __('%'), __('Present'), __('Absent'), __('Late'), __('Excused')],
                $rows,
            );
        }

        return view('teacher.reports.attendance', [
            'data' => $data,
            'studentRows' => ListQuery::paginateCollection($data['student_rows'], $request),
            'month' => $month,
            'print' => $request->boolean('print'),
            'filters' => array_filter([
                'month' => $month,
                'school_class_id' => $selectedClassId,
            ], fn ($value) => filled($value)),
            'schoolClasses' => SchoolClass::query()
                ->whereIn('id', $classIds)
                ->orderBy('code')
                ->get(),
            'selectedSchoolClassId' => $selectedClassId,
        ]);
    }
}
