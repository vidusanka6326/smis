<?php

namespace Database\Seeders;

use App\Actions\Officers\CreateOfficer;
use App\Enums\RoleName;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Seeder;

class OfficerUserSeeder extends Seeder
{
    /**
     * Seed a default officer account for local development.
     */
    public function run(): void
    {
        if (User::query()->role(RoleName::Officer)->exists()) {
            return;
        }

        app(CreateOfficer::class)->handle([
            'name' => 'Office Staff',
            'email' => 'officer@smis.test',
            'password' => 'password',
            'password_confirmation' => 'password',
            'status' => UserStatus::Active->value,
        ]);
    }
}
