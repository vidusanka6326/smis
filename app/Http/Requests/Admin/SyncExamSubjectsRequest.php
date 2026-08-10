<?php

namespace App\Http\Requests\Admin;

use App\Models\Exam;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SyncExamSubjectsRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Exam $exam */
        $exam = $this->route('exam');

        return $this->user()?->can('update', $exam) ?? false;
    }

    /**
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    public function rules(): array
    {
        return [
            'subjects' => ['required', 'array', 'min:1'],
            'subjects.*.subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'subjects.*.max_marks' => ['nullable', 'numeric', 'gt:0'],
            'subjects.*.pass_mark' => ['nullable', 'numeric', 'gte:0'],
        ];
    }
}
