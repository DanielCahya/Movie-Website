<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\HomeController;
use GuzzleHttp\Middleware;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MahasiswaController;
use App\Http\Controllers\ShowController;
use App\Models\Login;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::middleware(['guest'])->group(function(){
  Route::get('/',[LoginController::class, 'index'])->name('login');
Route::post('/',[LoginController::class, 'login']);
});


Route::middleware(['auth'])->group(function(){
  Route::get('/user',[AdminController::class, 'index']);
  Route::get('/tvshow',[AdminController::class, 'tvshow']);
  Route::get('/home',[AdminController::class, 'index']);
  Route::get('/animation',[AdminController::class, 'animation']);
  Route::get('/movieList',[AdminController::class, 'movieList']);
  Route::get('/show/{id}',[ShowController::class, 'show']);
  Route::get('/showani/{id}',[ShowController::class, 'showani']);
  Route::get('/showv/{id}',[ShowController::class, 'showtv']);
  Route::get('/movieshow/{id}',[ShowController::class, 'movieshow']);
  Route::get('/showdetailtv/{id}',[ShowController::class, 'showdetailtv']);
  Route::get('/showanime/{id}',[ShowController::class, 'showanime']);
  Route::get('/showcari/{id}',[ShowController::class, 'showcari']);
  Route::get('/admin',[LoginController::class,'data'])->middleware('multiAkses:admin,superadmin')->name('admin');
  Route::get('/list',[LoginController::class, 'data']);
  Route::get('/edit/{id}',[LoginController::class,'edit'])->middleware('multiAkses:admin,superadmin')->name('edit');
  Route::post('/updated-data/{id}',[LoginController::class, 'updated']);
  Route::delete('/delete-user/{id}', [LoginController::class, 'deleteUser'])->middleware('multiAkses:admin,superadmin')->name('delete-user');
});

Route::post('/registerproses',[LoginController::class, 'create']);
// Route::post('/registerproses',function(){
//   User::create([
//     'email' => request('email'),
//     'username' => request('username'),
//     'password' => Hash::make(request('password'))
//   ]);
//   return redirect('/');
// });
Route::get('/register',[LoginController::class, 'register']);
Route::get('/logout',[LoginController::class, 'logout']);




// Route::get('/',[MovueController::class, 'l']);



// Route::get('/home',function(){
//    return redirect('/user');
// });