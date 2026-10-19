<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\MediaComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function show($username)
    {
        // Find the user by username, or throw a 404
        $user = User::where('username', $username)->firstOrFail();

        $recentActivity = MediaComment::with(['user', 'likes'])
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
            'username' => [
                'required',
                'string',
                'min:3',
                'max:30',
                'regex:/^[a-zA-Z0-9_.-]+$/',
                'unique:users,username,' . $user->id,
                Rule::notIn(['edit', 'watchlist', 'following', 'blocked', 'admin', 'login', 'logout', 'register', 'home', 'user', 'forum', 'watch', 'sitemap', 'privacy', 'terms']),
            ],
            'bio' => 'nullable|string|max:1000',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|dimensions:max_width=4000,max_height=4000|max:2048',
            'password' => ['nullable', 'string', 'confirmed', Password::min(8)],
        ], [
            'username.regex' => 'Username may only contain letters, numbers, dashes, underscores, and periods.',
            'username.not_in' => 'This username is reserved and cannot be used.',
            'avatar.dimensions' => 'Avatar dimensions must not exceed 4000x4000 pixels.',
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
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('profile.show', ['username' => $user->username])
            ->with('success', 'Profile updated successfully!');
    }

    public function deleteAvatar()
    {
        $user = Auth::user();

        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
            $user->avatar = null;
            $user->save();
            return back()->with('success', 'Avatar deleted successfully!');
        }

        return back()->with('error', 'No avatar found to delete.');
    }

    public function following()
    {
        $user = Auth::user();
        $followedUsers = $user->following()->orderBy('follows.created_at', 'desc')->get();
        return view('profile.following', compact('followedUsers'));
    }

    public function blocked()
    {
        return view('profile.blocked');
    }
}
