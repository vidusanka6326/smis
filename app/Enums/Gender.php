<?php

namespace App\Enums;

enum Gender: string
{
    case Girl = 'G';
    case Boy = 'B';

    public function label(): string
    {
        return match ($this) {
            self::Girl => 'Girl',
            self::Boy => 'Boy',
        };
    }
}
