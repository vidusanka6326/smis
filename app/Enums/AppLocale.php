<?php

namespace App\Enums;

enum AppLocale: string
{
    case English = 'en';
    case Sinhala = 'si';
    case Tamil = 'ta';

    public function nativeName(): string
    {
        return match ($this) {
            self::English => 'English',
            self::Sinhala => 'සිංහල',
            self::Tamil => 'தமிழ்',
        };
    }

    public function englishName(): string
    {
        return match ($this) {
            self::English => 'English',
            self::Sinhala => 'Sinhala',
            self::Tamil => 'Tamil',
        };
    }

    public static function current(): self
    {
        return self::tryFrom((string) app()->getLocale()) ?? self::English;
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
