<?php

use App\Http\Controllers\AdminController;

use GuzzleHttp\Middleware;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;

use App\Http\Controllers\ShowController;
use App\Http\Controllers\ForumController;
use App\Http\Controllers\WatchlistController;
use App\Http\Controllers\MediaCommentController;
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
  Route::get('/login',[LoginController::class, 'index'])->name('login');
  Route::post('/login',[LoginController::class, 'login']);
  Route::post('/registerproses',[LoginController::class, 'create']);
});

Route::middleware(['auth'])->group(function(){
    // Profile Routes
    Route::get('/profile/edit', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile/update', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile/avatar', [\App\Http\Controllers\ProfileController::class, 'deleteAvatar'])->name('profile.avatar.delete');
    
    // Profile Sidebar Additions
    Route::get('/profile/watchlist', [WatchlistController::class, 'index'])->name('profile.watchlist');
    
    Route::get('/profile/following', [\App\Http\Controllers\ProfileController::class, 'following'])->name('profile.following');
    Route::get('/profile/blocked', [\App\Http\Controllers\ProfileController::class, 'blocked'])->name('profile.blocked');
    Route::get('/profile/{username}', [\App\Http\Controllers\ProfileController::class, 'show'])->name('profile.show');
    
    // Follow System
    Route::post('/profile/{user}/follow', [\App\Http\Controllers\FollowController::class, 'toggle'])->name('follow.toggle');
});

// Publicly accessible pages
Route::get('/',[AdminController::class, 'index']);
Route::get('/home',[AdminController::class, 'index']);
Route::get('/tvshow',[AdminController::class, 'tvshow']);
Route::get('/animation',[AdminController::class, 'animation']);
Route::get('/movieList',[AdminController::class, 'movieList']);

// Forum Public Routes
Route::get('/forum', [ForumController::class, 'index'])->name('forum.index');
Route::get('/forum/category/{slug}', [ForumController::class, 'category'])->name('forum.category');
Route::get('/forum/thread/{slug}', [ForumController::class, 'thread'])->name('forum.thread');

// SEO Sitemap
Route::get('/sitemap.xml', [\App\Http\Controllers\SitemapController::class, 'index']);


// Consolidated Details Endpoint
Route::get('/watch/{type}/{id}', [ShowController::class, 'watch'])->name('watch');

// Require auth
Route::middleware(['auth'])->group(function(){
  Route::get('/user',[AdminController::class, 'index']);
  
  Route::get('/admin',[LoginController::class,'data'])->middleware('multiAkses:admin,superadmin')->name('admin');
  Route::get('/list',[LoginController::class, 'data']);
  Route::get('/edit/{id}',[LoginController::class,'edit'])->middleware('multiAkses:admin,superadmin')->name('edit');
  Route::post('/updated-data/{id}',[LoginController::class, 'updated']);
  Route::delete('/delete-user/{id}', [LoginController::class, 'deleteUser'])->middleware('multiAkses:admin,superadmin')->name('delete-user');

  // Forum Auth Routes
  Route::get('/forum/create/thread', [ForumController::class, 'createThread'])->name('forum.create');
  Route::post('/forum/store/thread', [ForumController::class, 'storeThread'])->name('forum.store');
  Route::post('/forum/thread/{thread_id}/reply', [ForumController::class, 'storePost'])->name('forum.reply');

  // Watchlist Toggles
  Route::post('/watchlist/toggle', [WatchlistController::class, 'toggle'])->name('watchlist.toggle');

  // Media Comments
  Route::post('/media/comment', [MediaCommentController::class, 'store'])->name('media.comment.store');
  Route::put('/media/comment/{comment}', [MediaCommentController::class, 'update'])->name('media.comment.update');
  Route::delete('/media/comment/{comment}', [MediaCommentController::class, 'destroy'])->name('media.comment.destroy');
  Route::post('/media/comment/{comment}/react', [MediaCommentController::class, 'toggleReaction'])->name('media.comment.react');
});

Route::get('/logout',[LoginController::class, 'logout']);
