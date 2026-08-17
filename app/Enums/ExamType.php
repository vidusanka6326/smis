<?php

namespace App\Enums;

enum ExamType: string
{
    case TermTest = 'term_test';
    case Scholarship = 'scholarship';
    case Ol = 'ol';
    case Al = 'al';

    public function label(): string
    {
        return match ($this) {
            self::TermTest => __('Term test'),
            self::Scholarship => __('Scholarship'),
            self::Ol => __('O/L'),
            self::Al => __('A/L'),
        };
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
