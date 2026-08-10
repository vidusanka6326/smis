<?php

namespace App\Enums;

enum RoleName: string
{
    case Admin = 'admin';
    case Teacher = 'teacher';
    case Student = 'student';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
