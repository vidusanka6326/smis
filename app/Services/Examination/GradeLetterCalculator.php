<?php

namespace App\Services\Examination;

use App\Enums\GradeLetter;
use InvalidArgumentException;

/**
 * Maps percentage-of-max to a grade letter.
 *
 * Assumed scale (until product owner confirms):
 * A ≥ 75, B ≥ 65, C ≥ 55, S ≥ 40, otherwise F.
 */
class GradeLetterCalculator
{
    public function fromPercentage(float $percentage): GradeLetter
    {
        if ($percentage < 0 || $percentage > 100) {
            throw new InvalidArgumentException('Percentage must be between 0 and 100.');
        }

        return match (true) {
            $percentage >= 75.0 => GradeLetter::A,
            $percentage >= 65.0 => GradeLetter::B,
            $percentage >= 55.0 => GradeLetter::C,
            $percentage >= 40.0 => GradeLetter::S,
            default => GradeLetter::F,
        };
    }

    public function fromMarks(float $marksObtained, float $maxMarks): GradeLetter
    {
        if ($maxMarks <= 0) {
            throw new InvalidArgumentException('Max marks must be greater than zero.');
        }

        if ($marksObtained < 0) {
            throw new InvalidArgumentException('Marks obtained cannot be negative.');
        }

        if ($marksObtained > $maxMarks) {
            throw new InvalidArgumentException('Marks obtained cannot exceed max marks.');
        }

        $percentage = ($marksObtained / $maxMarks) * 100;

        return $this->fromPercentage($percentage);
    }
}
