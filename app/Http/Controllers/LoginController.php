<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class LoginController extends Controller
{
    public function index()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ], [
            'username.required' => 'Username is required.',
            'password.required' => 'Password is required.',
        ]);

        $infologin = [
            'username' => $request->input('username'),
            'password' => $request->input('password'),
        ];

        if (Auth::attempt($infologin)) {
            $request->session()->regenerate();
            if (in_array(Auth::user()->role, ['admin', 'superadmin'], true)) {
                return redirect()->intended('/admin');
            }
            return redirect()->intended('/home');
        }

        return redirect('/login')
            ->withErrors(['username' => 'Invalid username or password.'])
            ->withInput($request->only('username'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    public function data()
    {
        $this->authorize('viewAny', User::class);

        $userRole = Auth::user()->role;
        switch ($userRole) {
            case 'superadmin':
                $data = User::latest()->get();
                break;

            case 'admin':
                $data = User::where('role', 'user')->latest()->get();
                break;

            default:
                abort(403, 'Unauthorized access.');
        }

        return view('Admin.admin', [
            'data' => $data,
        ]);
    }

    public function edit($id)
    {
        $data = User::findOrFail($id);
        $this->authorize('update', $data);

        return view('Admin.edit', [
            'data' => $data,
        ]);
    }

    public function updated(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $this->authorize('update', $user);

        $request->validate([
            'email' => 'required|email|max:255|unique:users,email,' . (int) $user->id,
            'username' => [
                'required',
                'string',
                'min:3',
                'max:30',
                'regex:/^[a-zA-Z0-9_.-]+$/',
                'unique:users,username,' . (int) $user->id,
                Rule::notIn(['edit', 'watchlist', 'following', 'blocked', 'admin', 'login', 'logout', 'register', 'home', 'user', 'forum', 'watch', 'sitemap', 'privacy', 'terms']),
            ],
        ], [
            'username.required' => 'Username is required.',
            'username.unique' => 'This username is already taken.',
            'username.regex' => 'Username may only contain letters, numbers, dashes, underscores, and periods.',
            'username.not_in' => 'This username is reserved and cannot be used.',
            'email.required' => 'Email is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already in use.',
        ]);

        $user->update([
            'email' => $request->input('email'),
            'username' => $request->input('username'),
        ]);

        return redirect()->route('admin')->with('success', 'User information updated successfully.');
    }

    public function deleteUser($id)
    {
        $user = User::findOrFail($id);
        $this->authorize('delete', $user);

        $user->delete();

        return redirect()->back()->with('success', 'User deleted successfully.');
    }

    public function create(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255|unique:users,email',
            'username' => [
                'required',
                'string',
                'min:3',
                'max:30',
                'regex:/^[a-zA-Z0-9_.-]+$/',
                'unique:users,username',
                Rule::notIn(['edit', 'watchlist', 'following', 'blocked', 'admin', 'login', 'logout', 'register', 'home', 'user', 'forum', 'watch', 'sitemap', 'privacy', 'terms']),
            ],
            'password' => ['required', 'string', Password::min(8)],
        ], [
            'username.required' => 'Username is required.',
            'username.unique' => 'This username is already taken.',
            'username.regex' => 'Username may only contain letters, numbers, dashes, underscores, and periods.',
            'username.not_in' => 'This username is reserved and cannot be used.',
            'email.required' => 'Email is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already in use.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters.',
        ]);

        $user = new User([
            'email' => $request->input('email'),
            'username' => $request->input('username'),
            'password' => Hash::make($request->input('password')),
        ]);
        $user->role = 'user';
        $user->save();

        return redirect('/login')->with('success', 'Registration successful! Please login.');
    }
}
