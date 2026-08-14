<?php

namespace App\Services\Agent\Tools;

use App\Enums\PermissionName;
use App\Models\Report;
use App\Models\Student;
use App\Models\User;
use App\Services\Agent\AgentScope;
use App\Services\Reporting\AttendanceAnalyticsReport;
use App\Services\Reporting\ClassEnrollmentReport;
use App\Services\Reporting\DemographicsReport;
use App\Services\Reporting\ExaminationStatisticsReport;
use App\Services\Reporting\ExamResultsReport;
use App\Services\Reporting\PerformanceRankingService;
use App\Services\Reporting\ReportCatalog;
use App\Services\Reporting\StaffAttendanceReport;
use App\Services\Reporting\TeacherAssignmentReport;
use App\Services\Reporting\TeacherReportScope;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;

class GetReportDataTool extends AbstractAgentTool
{
    public function __construct(
        private AgentScope $scope,
        private ReportCatalog $catalog,
        private AttendanceAnalyticsReport $attendance,
        private DemographicsReport $demographics,
        private ClassEnrollmentReport $enrollment,
        private ExaminationStatisticsReport $examination,
        private ExamResultsReport $examResults,
        private PerformanceRankingService $ranking,
        private StaffAttendanceReport $staffAttendance,
        private TeacherAssignmentReport $assignments,
        private TeacherReportScope $teacherReportScope,
    ) {}

    public function name(): string
    {
        return 'get_report_data';
    }

    public function description(): string
    {
        return 'Fetch a school report the user is allowed to view. Keys: attendance, at-risk, staff-attendance (office), demographics, enrollment, examination, exam-results, performance, assignments (office).';
    }

    public function parameters(): array
    {
        return $this->objectSchema([
            'report' => $this->stringParam('Catalog key such as attendance or performance.'),
            'month' => $this->stringParam('YYYY-MM for attendance reports. Defaults to this month.'),
            'class_code' => $this->stringParam('Optional class filter.'),
            'exam_name' => $this->stringParam('Required for examination, exam-results, and performance.'),
            'gender' => $this->stringParam('Optional G or B for enrollment.'),
        ], ['report']);
    }

    public function authorized(User $user): bool
    {
        return $user->can(PermissionName::ViewReports->value);
    }

    public function handle(User $user, array $arguments): array
    {
        Gate::forUser($user)->authorize('viewAny', Report::class);

        $key = $this->stringArg($arguments, 'report');

        if ($key === null) {
            return ['ok' => false, 'error' => 'report is required.', 'available' => $this->availableKeys($user)];
        }

        $allowed = $this->availableKeys($user);

        if (! in_array($key, $allowed, true)) {
            return ['ok' => false, 'error' => 'That report is not available for your role.', 'available' => $allowed];
        }

        $classIds = $this->classIds($user, $arguments);

        $data = match ($key) {
            'attendance', 'at-risk' => $this->attendanceData($key, $arguments, $classIds),
            'staff-attendance' => $this->staffData($arguments),
            'demographics' => $this->demographics->summarize($classIds),
            'enrollment' => [
                'rows' => array_slice($this->enrollment->rows(
                    $classIds,
                    null,
                    $this->stringArg($arguments, 'gender'),
                ), 0, 40),
            ],
            'examination', 'exam-results', 'performance' => $this->examData($user, $key, $arguments, $classIds),
            'assignments' => [
                'rows' => array_slice($this->assignments->rows($this->scope->currentAcademicYearId()), 0, 40),
            ],
            default => null,
        };

        if ($data === null) {
            return ['ok' => false, 'error' => 'Unknown report key.', 'available' => $allowed];
        }

        if (isset($data['error']) && is_string($data['error'])) {
            return ['ok' => false, 'error' => $data['error']];
        }

        return [
            'ok' => true,
            'report' => $key,
            'data' => $data,
        ];
    }

    /**
     * @return list<string>
     */
    private function availableKeys(User $user): array
    {
        $items = $user->isTeacher() ? $this->catalog->forTeacher() : $this->catalog->forAdmin();

        return array_column($items, 'key');
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return list<int>|null
     */
    private function classIds(User $user, array $arguments): ?array
    {
        $classCode = $this->stringArg($arguments, 'class_code');

        if ($classCode !== null) {
            return [$this->scope->resolveClass($user, $classCode)->id];
        }

        return $this->scope->accessibleClassIds($user);
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @param  list<int>|null  $classIds
     * @return array<string, mixed>
     */
    private function attendanceData(string $key, array $arguments, ?array $classIds): array
    {
        $month = $this->stringArg($arguments, 'month') ?? now()->format('Y-m');
        $start = Carbon::createFromFormat('Y-m', $month)?->startOfMonth();

        if ($start === null) {
            return ['error' => 'month must be YYYY-MM.'];
        }

        $report = $this->attendance->forMonth($start, $start->copy()->endOfMonth(), $classIds);

        if ($key === 'at-risk') {
            return [
                'month' => $start->format('Y-m'),
                'threshold' => AttendanceAnalyticsReport::AT_RISK_THRESHOLD,
                'at_risk' => array_slice($report['at_risk'], 0, 40),
                'count' => count($report['at_risk']),
            ];
        }

        return [
            'month' => $start->format('Y-m'),
            'summary' => $report['summary'],
            'class_rows' => $report['class_rows'],
            'student_rows' => array_slice($report['student_rows'], 0, 40),
        ];
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array<string, mixed>
     */
    private function staffData(array $arguments): array
    {
        $month = $this->stringArg($arguments, 'month') ?? now()->format('Y-m');
        $start = Carbon::createFromFormat('Y-m', $month)?->startOfMonth();

        if ($start === null) {
            return ['error' => 'month must be YYYY-MM.'];
        }

        return [
            'month' => $start->format('Y-m'),
            'rows' => $this->staffAttendance->forMonth($start, $start->copy()->endOfMonth()),
        ];
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @param  list<int>|null  $classIds
     * @return array<string, mixed>
     */
    private function examData(User $user, string $key, array $arguments, ?array $classIds): array
    {
        $examName = $this->stringArg($arguments, 'exam_name');

        if ($examName === null) {
            return ['error' => 'exam_name is required for this report.'];
        }

        $exam = $this->scope->resolveExam($user, $examName);
        $studentIds = null;

        if ($user->isTeacher() && $user->teacher) {
            $studentIds = $this->teacherReportScope->accessibleStudentIds($user->teacher);
        }

        if ($classIds !== null) {
            $classStudentIds = array_values(array_map(
                fn (mixed $id): int => (int) $id,
                Student::query()->whereIn('current_class_id', $classIds)->pluck('id')->all(),
            ));
            $studentIds = $studentIds === null
                ? $classStudentIds
                : array_values(array_intersect($studentIds, $classStudentIds));
        }

        if ($key === 'examination') {
            return $this->examination->forExam($exam, null, $studentIds);
        }

        if ($key === 'exam-results') {
            return [
                'exam' => $exam->name,
                'rows' => array_slice($this->examResults->forExam($exam, null, $studentIds), 0, 40),
            ];
        }

        return $this->ranking->forExam($exam, null, $studentIds);
    }
}
