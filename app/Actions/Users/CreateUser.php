<?php

namespace App\Actions\Users;

use App\Enums\RoleName;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateUser
{
    /**
     * Create a user and assign a Spatie role inside a transaction.
     *
     * @param  array{name: string, email: string, password: string, role: string, status?: string}  $data
     */
    public function handle(array $data): User
    {
        $role = RoleName::tryFrom($data['role']);

        if ($role === null) {
            throw ValidationException::withMessages([
                'role' => __('The selected role is invalid.'),
            ]);
        }

        return DB::transaction(function () use ($data, $role): User {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'status' => UserStatus::tryFrom($data['status'] ?? UserStatus::Active->value) ?? UserStatus::Active,
                'email_verified_at' => now(),
            ]);

            $user->assignRole($role);

            return $user->refresh();
        });
    }
}
