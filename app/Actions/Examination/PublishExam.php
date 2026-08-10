<?php

namespace App\Actions\Examination;

use App\Enums\ActivityAction;
use App\Models\Exam;
use App\Services\Audit\ActivityLogger;
use Illuminate\Validation\ValidationException;

class PublishExam
{
    public function __construct(private ActivityLogger $activityLogger) {}

    public function handle(Exam $exam, bool $publish = true): Exam
    {
        if ($publish) {
            if ($exam->examSubjects()->doesntExist()) {
                throw ValidationException::withMessages([
                    'exam' => __('Add at least one subject before publishing.'),
                ]);
            }

            $exam->update(['published_at' => now()]);
            $action = ActivityAction::ExamPublished;
            $description = __('Published exam :name.', ['name' => $exam->name]);
        } else {
            $exam->update(['published_at' => null]);
            $action = ActivityAction::ExamUnpublished;
            $description = __('Unpublished exam :name.', ['name' => $exam->name]);
        }

        $exam = $exam->refresh();

        $this->activityLogger->log(
            $action,
            $description,
            $exam,
            [
                'exam_id' => $exam->id,
                'published' => $publish,
            ],
        );

        return $exam;
    }
}
