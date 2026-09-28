<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function show($username)
    {
        // Find the user by username, or throw a 404
        $user = User::where('username', $username)->firstOrFail();
        
        $recentActivity = \App\Models\MediaComment::with('user', 'likes')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        return view('profile.show', compact('user', 'recentActivity'));
    }

    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $request->validate([
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'bio' => 'nullable|string|max:1000',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        if ($request->hasFile('avatar')) {
            // Delete old avatar if it exists
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
        }

        $user->username = $request->username;
        $user->bio = $request->bio;
        
        if ($request->filled('password')) {
            $user->password = \Illuminate\Support\Facades\Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('profile.show', ['username' => $user->username])
            ->with('success', 'Profile updated successfully!');
    }

    public function following()
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        $followedUsers = $user->following()->orderBy('follows.created_at', 'desc')->get();
        return view('profile.following', compact('followedUsers'));
    }

    public function blocked()
    {
        return view('profile.blocked');
    }
}
