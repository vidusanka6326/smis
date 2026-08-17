<?php

namespace App\Enums;

enum EnrollmentStatus: string
{
    case Active = 'active';
    case Completed = 'completed';
    case Transferred = 'transferred';
    case Withdrawn = 'withdrawn';

    public function label(): string
    {
        return match ($this) {
            self::Active => __('Active'),
            self::Completed => __('Completed'),
            self::Transferred => __('Transferred'),
            self::Withdrawn => __('Withdrawn'),
        };
    }
}
