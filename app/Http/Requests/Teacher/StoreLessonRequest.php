<?php

namespace App\Http\Requests\Teacher;

use App\Models\Lesson;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreLessonRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Lesson::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'youtube_url' => ['nullable', 'string', 'url', 'max:255'],
            'school_class_ids' => ['required', 'array', 'min:1'],
            'school_class_ids.*' => ['required', 'integer', 'exists:classes,id'],
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
        ];
    }

    /**
     * Validate teacher assignments.
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if (! $this->user() || ! $this->user()->teacher) {
                    return;
                }

                $assignments = $this->user()->teacher->assignments()->get();
                $teacherSubjectIds = $assignments->pluck('subject_id')->filter()->unique()->toArray();
                $teacherClassIds = $assignments->pluck('school_class_id')->filter()->unique()->toArray();

                if (! in_array((int) $this->subject_id, $teacherSubjectIds)) {
                    $validator->errors()->add('subject_id', __('You are not assigned to this subject.'));
                }

                $invalidClasses = collect($this->school_class_ids)->filter(fn ($id) => ! in_array((int) $id, $teacherClassIds));
                if ($invalidClasses->isNotEmpty()) {
                    $validator->errors()->add('school_class_ids', __('You are not assigned to one or more selected classes.'));
                }
            },
        ];
    }
}
