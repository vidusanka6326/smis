<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Gender;
use App\Http\Controllers\Concerns\RespondsWithReportExport;
use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\Report;
use App\Models\SchoolClass;
use App\Services\Reporting\ClassEnrollmentReport;
use App\Services\Reporting\ReportCsvExporter;
use App\Services\Reporting\ReportPdfExporter;
use App\Support\ListQuery;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class EnrollmentReportController extends Controller
{
    use RespondsWithReportExport;

    public function __invoke(
        Request $request,
        ClassEnrollmentReport $report,
        ReportCsvExporter $csv,
        ReportPdfExporter $pdf,
    ): View|Response {
        $this->authorize('viewAny', Report::class);

        $classId = $request->filled('school_class_id') ? $request->integer('school_class_id') : null;
        $gradeId = $request->filled('grade_id') ? $request->integer('grade_id') : null;
        $gender = $request->string('gender')->toString() ?: null;

        $enrollment = $report->rows(
            $classId !== null ? [$classId] : null,
            $gradeId,
            $gender,
        );

        $headers = [__('Admission no.'), __('Name'), __('Gender'), __('Class'), __('Grade'), __('Date of birth'), __('Guardian'), __('Phone')];
        $rows = collect($enrollment)->map(fn (array $row): array => [
            $row['admission_no'],
            $row['name'],
            $row['gender'],
            $row['class'],
            $row['grade'],
            $row['date_of_birth'],
            $row['guardian_name'],
            $row['guardian_phone'],
        ]);

        $exported = $this->exportIfRequested(
            $request,
            $csv,
            $pdf,
            'class-enrollment',
            $headers,
            $rows,
            __('Class enrollment'),
            [['title' => __('Students'), 'headers' => $headers, 'rows' => $rows->all()]],
            null,
            'landscape',
        );

        if ($exported !== null) {
            return $exported;
        }

        return view('reports.enrollment', [
            'rows' => ListQuery::paginateCollection($enrollment, $request),
            'filters' => array_filter([
                'school_class_id' => $classId,
                'grade_id' => $gradeId,
                'gender' => $gender,
            ], fn ($value) => filled($value)),
            'schoolClasses' => SchoolClass::query()->orderBy('code')->get(),
            'grades' => Grade::query()->orderBy('number')->get(),
            'genders' => Gender::cases(),
            'selectedSchoolClassId' => $classId,
            'selectedGradeId' => $gradeId,
            'selectedGender' => $gender,
            'action' => route('admin.reports.enrollment'),
            'heading' => __('Class enrollment'),
            'description' => __('Student register with class, gender, and guardian details.'),
            'catalogRoute' => 'admin.reports.dashboard',
            'exportQuery' => [
                'school_class_id' => $classId,
                'grade_id' => $gradeId,
                'gender' => $gender,
            ],
        ]);
    }
}
