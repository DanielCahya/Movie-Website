<?php

namespace App\Policies;

use App\Models\Thread;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ThreadPolicy
{
    use HandlesAuthorization;

    public function update(User $user, Thread $thread): bool
    {
        return $user->id === $thread->user_id;
    }

    public function delete(User $user, Thread $thread): bool
    {
        return $user->id === $thread->user_id || in_array($user->role, ['admin', 'superadmin'], true);
    }
}

