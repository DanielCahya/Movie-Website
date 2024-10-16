<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;

class Login extends Model
{
    public static function get_all_users(){
        $data = User::get();
        return $data;
    }
    public static function get_user($uname){
        $data = User::where('uname', $uname)->first();

        return $data;
    }
}
