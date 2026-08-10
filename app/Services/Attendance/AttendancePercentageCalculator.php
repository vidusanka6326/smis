<?php

namespace App\Services\Attendance;

use App\Enums\AttendanceStatus;
use InvalidArgumentException;

class AttendancePercentageCalculator
{
    /**
     * Compute attendance percentage from a list of statuses.
     *
     * Rules (assumed until product owner confirms):
     * - Present and Late count as attended.
     * - Absent counts against attendance.
     * - Excused is excluded from both numerator and denominator.
     *
     * @param  list<AttendanceStatus|string>  $statuses
     */
    public function percentage(array $statuses): float
    {
        $attended = 0;
        $counted = 0;

        foreach ($statuses as $status) {
            $enum = $status instanceof AttendanceStatus
                ? $status
                : AttendanceStatus::from((string) $status);

            if (! $enum->countsTowardDenominator()) {
                continue;
            }

            $counted++;

            if ($enum->countsAsAttended()) {
                $attended++;
            }
        }

        if ($counted === 0) {
            return 0.0;
        }

        return round(($attended / $counted) * 100, 2);
    }

    /**
     * @param  array{present?: int, absent?: int, late?: int, excused?: int}  $counts
     */
    public function percentageFromCounts(array $counts): float
    {
        $statuses = [];

        foreach ([
            AttendanceStatus::Present->value => AttendanceStatus::Present,
            AttendanceStatus::Absent->value => AttendanceStatus::Absent,
            AttendanceStatus::Late->value => AttendanceStatus::Late,
            AttendanceStatus::Excused->value => AttendanceStatus::Excused,
        ] as $key => $enum) {
            $n = (int) ($counts[$key] ?? 0);

            if ($n < 0) {
                throw new InvalidArgumentException('Attendance counts cannot be negative.');
            }

            for ($i = 0; $i < $n; $i++) {
                $statuses[] = $enum;
            }
        }

        return $this->percentage($statuses);
    }
}
