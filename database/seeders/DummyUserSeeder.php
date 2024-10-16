<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DummyUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $userData = [
            [
                'username'=>'user',
                'email'=>'user@gmail.com',
                'role'=>'user',
                'password'=>bcrypt('user')
            ],
            [
                'username'=>'admin',
                'email'=>'admin@gmail.com',
                'role'=>'admin',
                'password'=>bcrypt('admin')
            ],
            [
                'username'=>'superadmin',
                'email'=>'superadmin@gmail.com',
                'role'=>'superadmin',
                'password'=>bcrypt('superadmin')
            ],
        ];
        foreach($userData as $key => $val){
            User::create($val);
        }
    }
}
