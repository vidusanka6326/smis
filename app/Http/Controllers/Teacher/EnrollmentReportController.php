<?php

namespace App\Http\Controllers\Teacher;

use App\Enums\Gender;
use App\Http\Controllers\Concerns\RespondsWithReportExport;
use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\Report;
use App\Models\SchoolClass;
use App\Services\Reporting\ClassEnrollmentReport;
use App\Services\Reporting\ReportCsvExporter;
use App\Services\Reporting\ReportPdfExporter;
use App\Services\Reporting\TeacherReportScope;
use App\Support\ListQuery;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class EnrollmentReportController extends Controller
{
    use RespondsWithReportExport;

    public function __invoke(
        Request $request,
        TeacherReportScope $scope,
        ClassEnrollmentReport $report,
        ReportCsvExporter $csv,
        ReportPdfExporter $pdf,
    ): View|Response {
        $this->authorize('viewAny', Report::class);

        $teacher = $request->user()->teacher;
        abort_unless($teacher !== null, 403);

        $classIds = $scope->accessibleClassIds($teacher);
        $selectedClassId = $request->filled('school_class_id') ? $request->integer('school_class_id') : null;

        if ($selectedClassId !== null && ! in_array($selectedClassId, $classIds, true)) {
            abort(403);
        }

        $gradeId = $request->filled('grade_id') ? $request->integer('grade_id') : null;
        $gender = $request->string('gender')->toString() ?: null;

        $enrollment = $report->rows(
            $selectedClassId !== null ? [$selectedClassId] : $classIds,
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
            'class-roster',
            $headers,
            $rows,
            __('Class roster'),
            [['title' => __('Students'), 'headers' => $headers, 'rows' => $rows->all()]],
            null,
            'landscape',
        );

        if ($exported !== null) {
            return $exported;
        }

        $accessibleClasses = SchoolClass::query()->whereIn('id', $classIds)->orderBy('code')->get();

        return view('reports.enrollment', [
            'rows' => ListQuery::paginateCollection($enrollment, $request),
            'filters' => array_filter([
                'school_class_id' => $selectedClassId,
                'grade_id' => $gradeId,
                'gender' => $gender,
            ], fn ($value) => filled($value)),
            'schoolClasses' => $accessibleClasses,
            'grades' => Grade::query()
                ->whereIn('id', $accessibleClasses->pluck('grade_id')->filter()->unique())
                ->orderBy('number')
                ->get(),
            'genders' => Gender::cases(),
            'selectedSchoolClassId' => $selectedClassId,
            'selectedGradeId' => $gradeId,
            'selectedGender' => $gender,
            'heading' => __('Class roster'),
            'description' => __('Students in your assigned classes.'),
            'action' => route('teacher.reports.enrollment'),
            'catalogRoute' => 'teacher.reports.dashboard',
            'exportQuery' => [
                'school_class_id' => $selectedClassId,
                'grade_id' => $gradeId,
                'gender' => $gender,
            ],
        ]);
    }
}
