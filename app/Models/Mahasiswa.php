<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mahasiswa extends Model
{
    protected $table = 'data_mahasiswa';

    public static function getAllMahasiswa(){

        $data = Mahasiswa::get();

        return $data;
    }

    public static function getNamaNim($nim){
        // SELECT * FROM data_mahasiswa;
        $data = Mahasiswa::where('nim',$nim)
        -> first();
        // Kalau cuma mengambil data teratas pakai FIRST
        // JIka mau mengambil semua data memakai GET

        return $data;
    }
}
