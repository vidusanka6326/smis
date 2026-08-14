<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TeacherAssignmentRole;
use App\Http\Controllers\Concerns\RespondsWithReportExport;
use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Report;
use App\Models\SchoolClass;
use App\Services\Reporting\ReportCsvExporter;
use App\Services\Reporting\ReportPdfExporter;
use App\Services\Reporting\TeacherAssignmentReport;
use App\Support\ListQuery;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class TeacherAssignmentReportController extends Controller
{
    use RespondsWithReportExport;

    public function __invoke(
        Request $request,
        TeacherAssignmentReport $report,
        ReportCsvExporter $csv,
        ReportPdfExporter $pdf,
    ): View|Response {
        $this->authorize('viewAny', Report::class);

        $yearId = $request->filled('academic_year_id')
            ? $request->integer('academic_year_id')
            : AcademicYear::query()->where('is_current', true)->value('id');
        $classId = $request->filled('school_class_id') ? $request->integer('school_class_id') : null;
        $role = $request->string('role')->toString() ?: null;

        $assignments = $report->rows($yearId !== null ? (int) $yearId : null, $classId, $role);

        $headers = [__('Teacher'), __('Employee no.'), __('Class'), __('Subject'), __('Role'), __('Academic year')];
        $rows = collect($assignments)->map(fn (array $row): array => [
            $row['teacher'],
            $row['employee_no'],
            $row['class'],
            $row['subject'],
            $row['role'],
            $row['academic_year'],
        ]);

        $exported = $this->exportIfRequested(
            $request,
            $csv,
            $pdf,
            'teacher-assignments',
            $headers,
            $rows,
            __('Teacher assignments'),
            [['title' => __('Assignments'), 'headers' => $headers, 'rows' => $rows->all()]],
            null,
            'landscape',
        );

        if ($exported !== null) {
            return $exported;
        }

        return view('reports.assignments', [
            'rows' => ListQuery::paginateCollection($assignments, $request),
            'filters' => array_filter([
                'academic_year_id' => $yearId,
                'school_class_id' => $classId,
                'role' => $role,
            ], fn ($value) => filled($value)),
            'academicYears' => AcademicYear::query()->orderByDesc('starts_on')->get(),
            'schoolClasses' => SchoolClass::query()->orderBy('code')->get(),
            'roles' => TeacherAssignmentRole::cases(),
            'selectedYearId' => $yearId,
            'selectedSchoolClassId' => $classId,
            'selectedRole' => $role,
            'action' => route('admin.reports.assignments'),
            'catalogRoute' => 'admin.reports.dashboard',
            'exportQuery' => [
                'academic_year_id' => $yearId,
                'school_class_id' => $classId,
                'role' => $role,
            ],
        ]);
    }
}
