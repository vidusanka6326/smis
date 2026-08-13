<?php

namespace App\Services\Reporting;

use App\Enums\AttendanceStatus;
use App\Models\AttendanceSession;
use App\Models\StudentAttendance;
use App\Services\Attendance\AttendancePercentageCalculator;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class AttendanceAnalyticsReport
{
    /**
     * Assumption: students below this monthly attendance % need attention.
     */
    public const AT_RISK_THRESHOLD = 80.0;

    public function __construct(private AttendancePercentageCalculator $calculator) {}

    /**
     * @param  list<int>|null  $schoolClassIds
     * @return array{
     *     month: string,
     *     class_rows: list<array{school_class_id: int, code: string, percentage: float, present: int, absent: int, late: int, excused: int}>,
     *     student_rows: list<array{student_id: int, name: string, class: string, percentage: float, present: int, absent: int, late: int, excused: int}>,
     *     at_risk: list<array{student_id: int, name: string, class: string, percentage: float, present: int, absent: int, late: int, excused: int}>,
     *     summary: array{tracked_students: int, class_average: float|null, at_risk_count: int, threshold: float}
     * }
     */
    public function forMonth(CarbonInterface $monthStart, CarbonInterface $monthEnd, ?array $schoolClassIds = null): array
    {
        $sessions = AttendanceSession::query()
            ->with('schoolClass')
            ->where('scope', AttendanceSession::SCOPE_CLASS)
            ->whereDate('date', '>=', $monthStart->toDateString())
            ->whereDate('date', '<=', $monthEnd->toDateString())
            ->when($schoolClassIds !== null, fn ($q) => $q->whereIn('school_class_id', $schoolClassIds))
            ->get();

        $sessionIds = $sessions->pluck('id');

        $attendances = StudentAttendance::query()
            ->with(['student.user', 'student.currentClass', 'attendanceSession.schoolClass'])
            ->whereIn('attendance_session_id', $sessionIds)
            ->get();

        $byClass = $attendances->groupBy(fn (StudentAttendance $row) => $row->attendanceSession?->school_class_id);
        $classRows = [];

        foreach ($byClass as $classId => $rows) {
            /** @var Collection<int, StudentAttendance> $rows */
            $counts = $this->countStatuses($rows->pluck('status'));
            $classRows[] = [
                'school_class_id' => (int) $classId,
                'code' => $rows->first()?->attendanceSession?->schoolClass?->code ?? (string) $classId,
                'percentage' => $this->calculator->percentage($rows->pluck('status')->all()),
                'present' => $counts[AttendanceStatus::Present->value],
                'absent' => $counts[AttendanceStatus::Absent->value],
                'late' => $counts[AttendanceStatus::Late->value],
                'excused' => $counts[AttendanceStatus::Excused->value],
            ];
        }

        $byStudent = $attendances->groupBy('student_id');
        $studentRows = [];

        foreach ($byStudent as $studentId => $rows) {
            /** @var Collection<int, StudentAttendance> $rows */
            $student = $rows->first()?->student;
            $counts = $this->countStatuses($rows->pluck('status'));
            $studentRows[] = [
                'student_id' => (int) $studentId,
                'name' => $student?->user?->name ?? (string) $studentId,
                'class' => $student?->currentClass?->code ?? '—',
                'percentage' => $this->calculator->percentage($rows->pluck('status')->all()),
                'present' => $counts[AttendanceStatus::Present->value],
                'absent' => $counts[AttendanceStatus::Absent->value],
                'late' => $counts[AttendanceStatus::Late->value],
                'excused' => $counts[AttendanceStatus::Excused->value],
            ];
        }

        usort($classRows, fn (array $a, array $b): int => strcmp($a['code'], $b['code']));
        usort($studentRows, fn (array $a, array $b): int => $b['percentage'] <=> $a['percentage']);

        $atRisk = $this->atRiskFromRows($studentRows);

        return [
            'month' => $monthStart->format('Y-m'),
            'class_rows' => $classRows,
            'student_rows' => $studentRows,
            'at_risk' => $atRisk,
            'summary' => $this->summarizeRows($studentRows, $classRows),
        ];
    }

    /**
     * Pure helper for unit tests: filter students below the attendance threshold.
     *
     * @param  list<array{student_id: int, name: string, class: string, percentage: float, present: int, absent: int, late: int, excused: int}>  $studentRows
     * @return list<array{student_id: int, name: string, class: string, percentage: float, present: int, absent: int, late: int, excused: int}>
     */
    public function atRiskFromRows(array $studentRows, float $threshold = self::AT_RISK_THRESHOLD): array
    {
        $atRisk = array_values(array_filter(
            $studentRows,
            fn (array $row): bool => (float) $row['percentage'] < $threshold,
        ));

        usort($atRisk, fn (array $a, array $b): int => $a['percentage'] <=> $b['percentage']);

        return $atRisk;
    }

    /**
     * @param  list<array{percentage: float}>  $studentRows
     * @param  list<array{percentage: float}>  $classRows
     * @return array{tracked_students: int, class_average: float|null, at_risk_count: int, threshold: float}
     */
    public function summarizeRows(array $studentRows, array $classRows, float $threshold = self::AT_RISK_THRESHOLD): array
    {
        $classAverage = null;
        if ($classRows !== []) {
            $sum = 0.0;
            foreach ($classRows as $row) {
                $sum += (float) $row['percentage'];
            }
            $classAverage = round($sum / count($classRows), 1);
        }

        return [
            'tracked_students' => count($studentRows),
            'class_average' => $classAverage,
            'at_risk_count' => count($this->atRiskFromRows($studentRows, $threshold)),
            'threshold' => $threshold,
        ];
    }

    /**
     * @param  Collection<int, AttendanceStatus|string>  $statuses
     * @return array{present: int, absent: int, late: int, excused: int}
     */
    public function countStatuses(Collection $statuses): array
    {
        $counts = [
            AttendanceStatus::Present->value => 0,
            AttendanceStatus::Absent->value => 0,
            AttendanceStatus::Late->value => 0,
            AttendanceStatus::Excused->value => 0,
        ];

        foreach ($statuses as $status) {
            $enum = $status instanceof AttendanceStatus ? $status : AttendanceStatus::from((string) $status);
            $counts[$enum->value]++;
        }

        return $counts;
    }
}
