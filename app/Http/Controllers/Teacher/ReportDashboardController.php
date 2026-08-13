<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Report;
use App\Services\Reporting\AttendanceAnalyticsReport;
use App\Services\Reporting\DemographicsReport;
use App\Services\Reporting\ExaminationStatisticsReport;
use App\Services\Reporting\TeacherReportScope;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportDashboardController extends Controller
{
    public function __invoke(
        Request $request,
        TeacherReportScope $scope,
        DemographicsReport $demographics,
        AttendanceAnalyticsReport $attendance,
        ExaminationStatisticsReport $examination,
    ): View {
        $this->authorize('viewAny', Report::class);

        $teacher = $request->user()->teacher;
        abort_unless($teacher !== null, 403);

        $classIds = $scope->accessibleClassIds($teacher);
        $demo = $demographics->summarize($classIds);
        $monthStart = now()->startOfMonth();
        $attendanceSummary = $attendance->forMonth($monthStart, (clone $monthStart)->endOfMonth(), $classIds);

        $latestExam = Exam::query()->whereNotNull('published_at')->latest('published_at')->first();
        $studentIds = $scope->accessibleStudentIds($teacher);
        $examStats = $latestExam !== null
            ? $examination->forExam($latestExam, null, $studentIds)
            : null;

        return view('teacher.reports.dashboard', [
            'demographics' => $demo,
            'attendance' => $attendanceSummary,
            'exam' => $latestExam,
            'examStats' => $examStats,
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
            'chartAttendanceByClass' => [
                'labels' => array_column($attendanceSummary['class_rows'], 'code'),
                'data' => array_map(
                    fn (array $row): float => (float) ($row['percentage'] ?? 0),
                    $attendanceSummary['class_rows'],
                ),
            ],
        ]);
    }
}
