<?php

namespace App\Actions\Officers;

use App\Actions\Users\CreateUser;
use App\Enums\RoleName;
use App\Models\User;

class CreateOfficer
{
    public function __construct(private CreateUser $createUser) {}

    /**
     * Create an office-staff user with the officer role.
     *
     * @param  array{name: string, email: string, password: string, status?: string}  $data
     */
    public function handle(array $data): User
    {
        return $this->createUser->handle([
            ...$data,
            'role' => RoleName::Officer->value,
        ]);
    }
}
