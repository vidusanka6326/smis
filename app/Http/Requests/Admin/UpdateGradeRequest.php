<?php

namespace App\Http\Requests\Admin;

use App\Models\Grade;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGradeRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Grade $grade */
        $grade = $this->route('grade');

        return $this->user()?->can('update', $grade) ?? false;
    }

    /**
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    public function rules(): array
    {
        /** @var Grade $grade */
        $grade = $this->route('grade');

        return [
            'number' => [
                'required',
                'integer',
                'min:1',
                'max:13',
                Rule::unique('grades', 'number')->ignore($grade->id),
            ],
            'name' => ['required', 'string', 'max:50'],
        ];
    }
}
