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
            self::TermTest => 'Term test',
            self::Scholarship => 'Scholarship',
            self::Ol => 'O/L',
            self::Al => 'A/L',
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
