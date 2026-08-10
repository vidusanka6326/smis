<?php

namespace App\Services\Examination;

use App\Enums\GradeLetter;

/**
 * Combines grade-letter and pass/fail calculation for a single mark entry.
 *
 * @phpstan-type MarkResult array{grade_letter: GradeLetter, is_pass: bool, percentage: float}
 */
class MarksResultCalculator
{
    public function __construct(
        private GradeLetterCalculator $gradeLetterCalculator,
        private PassFailCalculator $passFailCalculator,
    ) {}

    /**
     * @return array{grade_letter: GradeLetter, is_pass: bool, percentage: float}
     */
    public function calculate(float $marksObtained, float $maxMarks, float $passMark): array
    {
        $percentage = round(($marksObtained / $maxMarks) * 100, 2);

        return [
            'grade_letter' => $this->gradeLetterCalculator->fromMarks($marksObtained, $maxMarks),
            'is_pass' => $this->passFailCalculator->passes($marksObtained, $passMark, $maxMarks),
            'percentage' => $percentage,
        ];
    }
}
