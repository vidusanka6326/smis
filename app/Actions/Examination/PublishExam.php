<?php

namespace App\Actions\Examination;

use App\Models\Exam;
use Illuminate\Validation\ValidationException;

class PublishExam
{
    public function handle(Exam $exam, bool $publish = true): Exam
    {
        if ($publish) {
            if ($exam->examSubjects()->doesntExist()) {
                throw ValidationException::withMessages([
                    'exam' => __('Add at least one subject before publishing.'),
                ]);
            }

            $exam->update(['published_at' => now()]);
        } else {
            $exam->update(['published_at' => null]);
        }

        return $exam->refresh();
    }
}
