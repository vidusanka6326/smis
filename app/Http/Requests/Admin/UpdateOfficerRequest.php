<?php

namespace App\Http\Requests\Admin;

use App\Concerns\ProfileValidationRules;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateOfficerRequest extends FormRequest
{
    use ProfileValidationRules;

    public function authorize(): bool
    {
        /** @var User $officer */
        $officer = $this->route('officer');

        return $this->user()?->can('updateOfficer', $officer) ?? false;
    }

    /**
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    public function rules(): array
    {
        /** @var User $officer */
        $officer = $this->route('officer');

        return [
            ...$this->profileRules($officer->id),
            'password' => ['nullable', 'string', Password::default(), 'confirmed'],
            'status' => ['required', 'string', Rule::enum(UserStatus::class)],
        ];
    }
}
