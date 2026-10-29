<?php

use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ShowController;
use App\Http\Controllers\ForumController;
use App\Http\Controllers\WatchlistController;
use App\Http\Controllers\MediaCommentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\SitemapController;

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

// Guest Routes (throttled login & registration)
Route::middleware(['guest'])->group(function () {
    Route::get('/login', [LoginController::class, 'index'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:5,1');
    Route::post('/registerproses', [LoginController::class, 'create'])->middleware('throttle:5,1');
});

// Authenticated Profile Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile/avatar', [ProfileController::class, 'deleteAvatar'])->name('profile.avatar.delete');

    Route::get('/profile/watchlist', [WatchlistController::class, 'index'])->name('profile.watchlist');
    Route::get('/profile/following', [ProfileController::class, 'following'])->name('profile.following');
    Route::get('/profile/blocked', [ProfileController::class, 'blocked'])->name('profile.blocked');
    Route::get('/profile/{username}', [ProfileController::class, 'show'])->name('profile.show');

    // Follow System
    Route::post('/profile/{user}/follow', [FollowController::class, 'toggle'])->name('follow.toggle');
});

// Public Pages
Route::get('/', [AdminController::class, 'index']);
Route::get('/home', [AdminController::class, 'index']);
Route::get('/tvshow', [AdminController::class, 'tvshow']);
Route::get('/animation', [AdminController::class, 'animation']);
Route::get('/movieList', [AdminController::class, 'movieList']);

// Clean URL Case Redirections
Route::redirect('/Animation', '/animation', 301);
Route::redirect('/MovieList', '/movieList', 301);
Route::redirect('/Tvshow', '/tvshow', 301);

// Forum Public Routes
Route::get('/forum', [ForumController::class, 'index'])->name('forum.index');
Route::get('/forum/category/{slug}', [ForumController::class, 'category'])->name('forum.category');
Route::get('/forum/thread/{slug}', [ForumController::class, 'thread'])->name('forum.thread');

// Media Details Endpoint
Route::get('/watch/{type}/{id}', [ShowController::class, 'watch'])->name('watch');

// SEO & Legal
Route::get('/sitemap.xml', [SitemapController::class, 'index']);
Route::view('/privacy', 'legal.privacy')->name('privacy');
Route::view('/terms', 'legal.terms')->name('terms');

// Authenticated Actions
Route::middleware(['auth'])->group(function () {
    // Redirection for /user
    Route::get('/user', fn () => redirect('/home'));

    // Admin & Superadmin User Management
    Route::get('/admin', [LoginController::class, 'data'])->middleware('multiAkses:admin,superadmin')->name('admin');
    Route::get('/list', fn () => redirect()->route('admin'))->middleware('multiAkses:admin,superadmin');
    Route::get('/edit/{id}', [LoginController::class, 'edit'])->middleware('multiAkses:admin,superadmin')->name('edit');
    Route::post('/updated-data/{id}', [LoginController::class, 'updated'])->middleware('multiAkses:admin,superadmin');
    Route::delete('/delete-user/{id}', [LoginController::class, 'deleteUser'])->middleware('multiAkses:admin,superadmin')->name('delete-user');

    // Forum Creation & Replies with rate-limiting and policies
    Route::get('/forum/create/thread', [ForumController::class, 'createThread'])->name('forum.create');
    Route::post('/forum/store/thread', [ForumController::class, 'storeThread'])->middleware('throttle:6,1')->name('forum.store');
    Route::delete('/forum/thread/{thread}', [ForumController::class, 'destroyThread'])->name('forum.thread.destroy');
    Route::post('/forum/thread/{thread_id}/reply', [ForumController::class, 'storePost'])->middleware('throttle:15,1')->name('forum.reply');
    Route::delete('/forum/post/{post}', [ForumController::class, 'destroyPost'])->name('forum.post.destroy');

    // Watchlist Toggles
    Route::post('/watchlist/toggle', [WatchlistController::class, 'toggle'])->name('watchlist.toggle');

    // Media Comments & Reactions
    Route::post('/media/comment', [MediaCommentController::class, 'store'])->middleware('throttle:15,1')->name('media.comment.store');
    Route::put('/media/comment/{comment}', [MediaCommentController::class, 'update'])->name('media.comment.update');
    Route::delete('/media/comment/{comment}', [MediaCommentController::class, 'destroy'])->name('media.comment.destroy');
    Route::post('/media/comment/{comment}/react', [MediaCommentController::class, 'toggleReaction'])->middleware('throttle:60,1')->name('media.comment.react');
});

// Logout (POST only, requires auth)
Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');
