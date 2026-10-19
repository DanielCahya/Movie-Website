<?php

namespace App\Policies;

use App\Models\MediaComment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MediaCommentPolicy
{
    use HandlesAuthorization;

    public function update(User $user, MediaComment $comment): bool
    {
        return $user->id === $comment->user_id;
    }

    public function delete(User $user, MediaComment $comment): bool
    {
        return $user->id === $comment->user_id || in_array($user->role, ['admin', 'superadmin'], true);
    }
}

