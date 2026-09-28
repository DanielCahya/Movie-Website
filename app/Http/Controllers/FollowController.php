<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FollowController extends Controller
{
    public function toggle(User $user)
    {
        $auth = Auth::user();
        
        // Cannot follow yourself
        if ($auth->id === $user->id) {
            return back();
        }
        
        if ($auth->isFollowing($user->id)) {
            $auth->following()->detach($user->id);
        } else {
            $auth->following()->attach($user->id);
        }
        
        return back();
    }
}
