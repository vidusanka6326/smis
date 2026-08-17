<?php

namespace App\Enums;

enum Gender: string
{
    case Girl = 'G';
    case Boy = 'B';

    public function label(): string
    {
        return match ($this) {
            self::Girl => __('Girl'),
            self::Boy => __('Boy'),
        };
    }
}
