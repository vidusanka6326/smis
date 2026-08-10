<?php

namespace App\Concerns;

use App\Enums\PermissionName;
use App\Models\User;

trait ChecksSystemConfigPermission
{
    public function viewAny(User $user): bool
    {
        return $this->canManageSystemConfig($user);
    }

    public function view(User $user, mixed $model): bool
    {
        return $this->canManageSystemConfig($user);
    }

    public function create(User $user): bool
    {
        return $this->canManageSystemConfig($user);
    }

    public function update(User $user, mixed $model): bool
    {
        return $this->canManageSystemConfig($user);
    }

    public function delete(User $user, mixed $model): bool
    {
        return $this->canManageSystemConfig($user);
    }

    public function restore(User $user, mixed $model): bool
    {
        return $this->canManageSystemConfig($user);
    }

    public function forceDelete(User $user, mixed $model): bool
    {
        return $this->canManageSystemConfig($user);
    }

    protected function canManageSystemConfig(User $user): bool
    {
        return $user->can(PermissionName::ManageSystemConfig->value);
    }
}
