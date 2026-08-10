<?php

namespace App\Enums;

enum AttendanceStatus: string
{
    case Present = 'present';
    case Absent = 'absent';
    case Late = 'late';
    case Excused = 'excused';

    public function label(): string
    {
        return match ($this) {
            self::Present => 'Present',
            self::Absent => 'Absent',
            self::Late => 'Late',
            self::Excused => 'Excused',
        };
    }

    /**
     * Whether this status counts as physically attended (present or late).
     */
    public function countsAsAttended(): bool
    {
        return match ($this) {
            self::Present, self::Late => true,
            self::Absent, self::Excused => false,
        };
    }

    /**
     * Whether this status is included in the attendance percentage denominator.
     *
     * Excused days are excluded from both numerator and denominator.
     */
    public function countsTowardDenominator(): bool
    {
        return $this !== self::Excused;
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
