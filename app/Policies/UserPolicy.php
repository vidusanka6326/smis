<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Enums\RoleName;
use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionName::ManageUsers->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        if ($user->is($model)) {
            return true;
        }

        return $user->can(PermissionName::ManageUsers->value);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(PermissionName::ManageUsers->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        if ($user->is($model)) {
            return true;
        }

        return $user->can(PermissionName::ManageUsers->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        if ($user->is($model)) {
            return false;
        }

        return $user->can(PermissionName::ManageUsers->value);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return $user->can(PermissionName::ManageUsers->value);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        if ($user->is($model)) {
            return false;
        }

        return $user->hasRole(RoleName::Admin) && $user->can(PermissionName::ManageUsers->value);
    }

    /**
     * Determine whether the user may assign roles when creating accounts.
     */
    public function assignRole(User $user): bool
    {
        return $user->hasRole(RoleName::Admin) && $user->can(PermissionName::ManageUsers->value);
    }
}
