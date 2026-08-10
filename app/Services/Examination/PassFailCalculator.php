<?php

namespace App\Services\Examination;

use InvalidArgumentException;

/**
 * Determines pass/fail from raw marks vs configured pass mark.
 */
class PassFailCalculator
{
    public function passes(float $marksObtained, float $passMark, float $maxMarks): bool
    {
        if ($maxMarks <= 0) {
            throw new InvalidArgumentException('Max marks must be greater than zero.');
        }

        if ($passMark < 0 || $passMark > $maxMarks) {
            throw new InvalidArgumentException('Pass mark must be between 0 and max marks.');
        }

        if ($marksObtained < 0) {
            throw new InvalidArgumentException('Marks obtained cannot be negative.');
        }

        if ($marksObtained > $maxMarks) {
            throw new InvalidArgumentException('Marks obtained cannot exceed max marks.');
        }

        return $marksObtained >= $passMark;
    }
}
