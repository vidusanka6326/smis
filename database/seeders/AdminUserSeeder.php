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
        $existing = User::query()->where('email', SriLankanDemoCatalog::ADMIN_EMAIL)->first();

        if ($existing !== null) {
            $existing->forceFill(['name' => 'Udana Vidushanka'])->save();

            return;
        }

        if (User::query()->role(RoleName::Admin)->exists()) {
            return;
        }

        app(CreateUser::class)->handle([
            'name' => 'Chamara Wickramasinghe',
            'email' => SriLankanDemoCatalog::ADMIN_EMAIL,
            'password' => SriLankanDemoCatalog::PASSWORD,
            'password_confirmation' => SriLankanDemoCatalog::PASSWORD,
            'role' => RoleName::Admin->value,
            'status' => UserStatus::Active->value,
        ]);
    }
}
