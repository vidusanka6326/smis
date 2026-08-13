<?php

namespace Database\Seeders;

use App\Actions\Officers\CreateOfficer;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Seeder;

class OfficerUserSeeder extends Seeder
{
    /**
     * Seed five office-staff accounts for local development.
     */
    public function run(): void
    {
        $createOfficer = app(CreateOfficer::class);

        foreach (SriLankanDemoCatalog::officers() as $officer) {
            $existing = User::query()->where('email', $officer['email'])->first();

            if ($existing !== null) {
                $existing->forceFill(['name' => $officer['name']])->save();

                continue;
            }

            $createOfficer->handle([
                'name' => $officer['name'],
                'email' => $officer['email'],
                'password' => SriLankanDemoCatalog::PASSWORD,
                'password_confirmation' => SriLankanDemoCatalog::PASSWORD,
                'status' => UserStatus::Active->value,
            ]);
        }
    }
}
