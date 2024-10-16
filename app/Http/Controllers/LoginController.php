<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    function index(){
        return view('login');
    }
    function login(Request $request){
        $request->validate([
            'username'=>'required',
            'password'=>'required'
        ],[
            'username.required' => 'Username wajib diisi',
            'password.required' => 'Password wajib diisi',
        ]);

        $infologin=[
            'username' => $request->username,
            'password' => $request->password,
        ];

        if (Auth::attempt($infologin)) {
            if (Auth::user()->role == 'admin') {
                return redirect('/admin');
            }elseif (Auth::user()->role == 'superadmin') {
                return redirect('/admin');
            }else {
                return redirect('/user');
            }
        }else{
            return redirect('')->withErrors('Username atau password tidak sesuai')->withInput();
        }
    }

    function logout(){
        Auth::logout();
        return redirect('/');
    }

    function register(){
        return view('register');
    }

    function data(){
        $userRole = Auth::user()->role;
        switch ($userRole) {
            case 'superadmin':
                $data = User::get();
                break;

            case 'admin':
                $data = User::where('role', 'user')->get();
                break;
            }
        return view('Admin.admin',[
            'data' => $data,
        ]);
    }
    function edit($id){
        $data = User::where('id','=',$id)->first();
        return view('Admin.edit',[
            'data' => $data,
        ]);
    }

    function updated(Request $request, $id)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email,' . $id,
            'username' => 'required',
        ]);

        $user = User::find($id);

        if (!$user) {
            return redirect()->back()->with('error', 'User not found');
        }

        $user->update([
            'email' => $request->input('email'),
            'username' => $request->input('username'),
        ]);

        return redirect()->route('admin')->with('success', 'User information updated successfully');
    }
    
    function deleteUser($id)
    {
        $user = User::find($id);

        if (!$user) {
            return redirect()->back()->with('error', 'User not found');
        }

        $user->delete();

        return redirect()->back()->with('success', 'User deleted successfully');
    }
    
    function create(Request $request){
        $request->validate([
            'email'=>'required|email|unique:users',
            'username'=>'required',
            'password'=>'required|min:6'
        ],[
            'email.email' => 'Tidak valid',
            'email.unique'=> 'Email sudah pernah diissi',
            'email.required' => 'Email wajib diisi',
            'username.required' => 'Username wajib diisi',
            'password.required' => 'Password wajib diisi',
            'password.min' => 'Password Min 6 karakter',
        ]);

        User::create([
            'email' => request('email'),
            'username' => request('username'),
            'password' => Hash::make(request('password'))
        ]);
        return redirect('/');
    }
}
