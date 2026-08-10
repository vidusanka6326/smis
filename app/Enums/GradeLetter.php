<?php

namespace App\Enums;

enum GradeLetter: string
{
    case A = 'A';
    case B = 'B';
    case C = 'C';
    case S = 'S';
    case F = 'F';

    public function label(): string
    {
        return $this->value;
    }

    public function isPassLetter(): bool
    {
        return $this !== self::F;
    }
}
