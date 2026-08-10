<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Report;
use App\Models\Student;
use App\Services\Reporting\AttendanceAnalyticsReport;
use App\Services\Reporting\DemographicsReport;
use App\Services\Reporting\ExaminationStatisticsReport;
use Illuminate\View\View;

class ReportDashboardController extends Controller
{
    public function __invoke(
        DemographicsReport $demographics,
        AttendanceAnalyticsReport $attendance,
        ExaminationStatisticsReport $examination,
    ): View {
        $this->authorize('viewAny', Report::class);

        $demo = $demographics->summarize();
        $monthStart = now()->startOfMonth();
        $attendanceSummary = $attendance->forMonth($monthStart, (clone $monthStart)->endOfMonth());

        $latestExam = Exam::query()->whereNotNull('published_at')->latest('published_at')->first();
        $examStats = $latestExam !== null
            ? $examination->forExam($latestExam)
            : null;

        return view('admin.reports.dashboard', [
            'demographics' => $demo,
            'attendance' => $attendanceSummary,
            'exam' => $latestExam,
            'examStats' => $examStats,
            'studentCount' => Student::query()->count(),
            'chartGender' => [
                'labels' => [__('Boys'), __('Girls')],
                'data' => [
                    $demo['by_gender']['B'] ?? 0,
                    $demo['by_gender']['G'] ?? 0,
                ],
            ],
            'chartGradeLetters' => [
                'labels' => array_keys($examStats['by_grade_letter'] ?? []),
                'data' => array_values($examStats['by_grade_letter'] ?? []),
            ],
        ]);
    }
}
