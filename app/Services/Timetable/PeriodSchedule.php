<?php

namespace App\Services\Timetable;

/**
 * Default school-day period clock times.
 *
 * Assumption: 8 × 40-minute periods starting 07:30, with a 20-minute
 * interval after period 4 (mid-morning break). Product can override later.
 */
class PeriodSchedule
{
    public const MAX_PERIODS = 8;

    public const PERIOD_MINUTES = 40;

    public const BREAK_AFTER_PERIOD = 4;

    public const BREAK_MINUTES = 20;

    public const DAY_START = '07:30';

    /**
     * @return array{start: string, end: string, label: string}
     */
    public function forPeriod(int $period): array
    {
        if ($period < 1 || $period > self::MAX_PERIODS) {
            throw new \InvalidArgumentException('Period must be between 1 and '.self::MAX_PERIODS.'.');
        }

        $minutesFromStart = ($period - 1) * self::PERIOD_MINUTES;

        if ($period > self::BREAK_AFTER_PERIOD) {
            $minutesFromStart += self::BREAK_MINUTES;
        }

        $start = $this->addMinutes(self::DAY_START, $minutesFromStart);
        $end = $this->addMinutes($start, self::PERIOD_MINUTES);

        return [
            'start' => $start,
            'end' => $end,
            'label' => "{$start} – {$end}",
        ];
    }

    /**
     * @return array<int, array{start: string, end: string, label: string}>
     */
    public function all(): array
    {
        $periods = [];

        foreach (range(1, self::MAX_PERIODS) as $period) {
            $periods[$period] = $this->forPeriod($period);
        }

        return $periods;
    }

    private function addMinutes(string $time, int $minutes): string
    {
        [$hour, $minute] = array_map('intval', explode(':', $time));
        $total = ($hour * 60) + $minute + $minutes;

        return sprintf('%02d:%02d', intdiv($total, 60) % 24, $total % 60);
    }
}
