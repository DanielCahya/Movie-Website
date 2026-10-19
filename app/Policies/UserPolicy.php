<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Map role names to integer hierarchy rank.
     */
    private function roleRank(?string $role): int
    {
        return match ($role) {
            'superadmin' => 3,
            'admin' => 2,
            'user' => 1,
            default => 0,
        };
    }

    /**
     * Determine whether the user can view the user management dashboard.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'superadmin'], true);
    }

    /**
     * Determine whether the user can update the target user.
     * Superadmins can update users and admins.
     * Admins can only update regular users (lower role).
     */
    public function update(User $user, User $target): bool
    {
        if ($user->role === 'superadmin') {
            return true;
        }

        if ($user->role === 'admin') {
            return $this->roleRank($user->role) > $this->roleRank($target->role);
        }

        return false;
    }

    /**
     * Determine whether the user can delete the target user.
     * - Cannot delete own account.
     * - Cannot delete any user with a higher or equal role.
     */
    public function delete(User $user, User $target): bool
    {
        if ($user->id === $target->id) {
            return false;
        }

        return $this->roleRank($user->role) > $this->roleRank($target->role);
    }
}

