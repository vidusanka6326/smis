<?php

namespace Database\Seeders;

use App\Actions\Users\CreateUser;
use App\Enums\RoleName;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Seed a default admin account for local development.
     */
    public function run(): void
    {
        if (User::query()->role(RoleName::Admin)->exists()) {
            return;
        }

        app(CreateUser::class)->handle([
            'name' => 'System Admin',
            'email' => 'admin@smis.test',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => RoleName::Admin->value,
            'status' => UserStatus::Active->value,
        ]);
    }
}
