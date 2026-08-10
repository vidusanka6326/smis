<?php

namespace App\Actions\Teachers;

use App\Enums\RoleName;
use App\Enums\UserStatus;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateTeacher
{
    /**
     * Create a teacher user account and profile in one transaction.
     *
     * @param  array{
     *     name: string,
     *     email: string,
     *     password: string,
     *     status?: string,
     *     employee_no: string,
     *     phone?: string|null
     * }  $data
     */
    public function handle(array $data): Teacher
    {
        return DB::transaction(function () use ($data): Teacher {
            $user = User::query()->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'status' => UserStatus::tryFrom($data['status'] ?? UserStatus::Active->value) ?? UserStatus::Active,
                'email_verified_at' => now(),
            ]);

            $user->assignRole(RoleName::Teacher);

            return Teacher::query()->create([
                'user_id' => $user->id,
                'employee_no' => $data['employee_no'],
                'phone' => $data['phone'] ?? null,
            ])->load('user');
        });
    }
}
