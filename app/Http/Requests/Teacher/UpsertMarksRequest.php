<?php

namespace App\Http\Requests\Teacher;

use App\Models\ExamSubject;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpsertMarksRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var ExamSubject $examSubject */
        $examSubject = $this->route('exam_subject');

        return $this->user()?->can('enterMarks', $examSubject) ?? false;
    }

    /**
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    public function rules(): array
    {
        return [
            'records' => ['required', 'array', 'min:1'],
            'records.*.student_id' => ['required', 'integer', 'exists:students,id'],
            'records.*.marks_obtained' => ['required', 'numeric', 'gte:0'],
        ];
    }
}
