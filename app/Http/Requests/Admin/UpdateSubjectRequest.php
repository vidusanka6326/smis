<?php

namespace App\Http\Requests\Admin;

use App\Models\Subject;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Subject $subject */
        $subject = $this->route('subject');

        return $this->user()?->can('update', $subject) ?? false;
    }

    /**
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    public function rules(): array
    {
        /** @var Subject $subject */
        $subject = $this->route('subject');

        return [
            'name' => ['required', 'string', 'max:100'],
            'code' => [
                'required',
                'string',
                'max:20',
                'alpha_dash',
                Rule::unique('subjects', 'code')->ignore($subject->id),
            ],
            'min_grade' => ['required', 'integer', 'min:1', 'max:13'],
            'max_grade' => ['required', 'integer', 'min:1', 'max:13'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $min = (int) $this->input('min_grade');
            $max = (int) $this->input('max_grade');

            if ($min > $max) {
                $validator->errors()->add('max_grade', __('The maximum grade must be greater than or equal to the minimum grade.'));
            }
        });
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('code')) {
            $this->merge([
                'code' => strtoupper((string) $this->input('code')),
            ]);
        }
    }
}
