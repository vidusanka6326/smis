<?php

namespace App\Actions\Officers;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateOfficer
{
    /**
     * Update an officer account. Password is optional.
     *
     * @param  array{name: string, email: string, password?: string|null, status: string}  $data
     */
    public function handle(User $officer, array $data): User
    {
        if (! $officer->isOfficer()) {
            throw ValidationException::withMessages([
                'officer' => __('The selected user is not an officer.'),
            ]);
        }

        return DB::transaction(function () use ($officer, $data): User {
            $payload = [
                'name' => $data['name'],
                'email' => $data['email'],
                'status' => UserStatus::from($data['status']),
            ];

            if (! empty($data['password'])) {
                $payload['password'] = $data['password'];
            }

            $officer->update($payload);

            return $officer->refresh();
        });
    }
}
