<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Services\Reporting\DemographicsReport;
use App\Services\Reporting\ReportCsvExporter;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DemographicsReportController extends Controller
{
    public function __invoke(Request $request, DemographicsReport $report, ReportCsvExporter $csv): View|StreamedResponse
    {
        $this->authorize('viewAny', Report::class);

        $subjectId = $request->filled('subject_id') ? $request->integer('subject_id') : null;
        $classIds = $request->filled('school_class_id')
            ? [$request->integer('school_class_id')]
            : null;

        $data = $report->summarize(
            schoolClassIds: $classIds,
            subjectId: $subjectId,
        );

        if ($request->string('export')->toString() === 'csv') {
            $rows = collect($data['by_class'])->map(fn (array $row): array => [
                $row['code'],
                $row['count'],
            ]);

            return $csv->download('demographics-by-class.csv', [__('Class'), __('Students')], $rows);
        }

        return view('admin.reports.demographics', [
            'data' => $data,
            'print' => $request->boolean('print'),
            'filters' => array_filter([
                'school_class_id' => $classIds[0] ?? null,
                'subject_id' => $subjectId,
            ], fn ($value) => filled($value)),
            'schoolClasses' => SchoolClass::query()->orderBy('code')->get(),
            'subjects' => Subject::query()->orderBy('name')->get(),
        ]);
    }
}
