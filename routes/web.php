<?php

use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\testController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Mail\test;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Http\Middleware\PreventBackHistory;
use App\Events\UserSessionChange;
use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

Route::middleware([ PreventBackHistory::class])->group(function () {
    Route::get('/', function () {
         
         return view('welcome');

    });
    // các route cần bảo vệ khác...
});

route::get('/dashboard', [ChatController::class, 'index'])->middleware(['auth'])->name('dashboard');



Route::get('/test-db', function () {
    try {
        DB::connection()->getPdo();
        return "Kết nối database thành công!";
    } catch (\Exception $e) {
        return "Lỗi kết nối: " . $e->getMessage();
    }
});

Route::get('/login', [LoginController::class, 'showLoginForm']
)->name('login');

Route::post('/login', [LoginController::class, 'login']
);

Route::get('/register',[RegisterController::class, 'showRegisterForm'])->name('register');

Route::post('/register',[RegisterController::class, 'Register']);

Route::post('/logout', [LoginController::class, 'destroy']);

Route::get('/check-db', function () {
    try {
        $count = User::count();
        return "Connected! User count: $count";
    } catch (\Exception $e) {
        return 'Database connection failed: ' . $e->getMessage();
    }
});

Route::get('/login/google', [SocialAuthController::class, 'redirectToGoogle'])->name('googleLogin');
Route::get('/login/google/callback',[SocialAuthController::class, 'handleGoogleCallback'])->name('GoogleCallback');

Route::get('/login/facebook', [SocialAuthController::class, 'redirectToFaceBook'])->name('faceBookLogin');
Route::get('/login/facebook/callback',[SocialAuthController::class, 'handleFaceBookCallback'])->name('FacebookCallback');

use Illuminate\Http\Request;

Route::get('/notification_send', function() {
    UserSessionChange::dispatch('User logged in', 'success');
    //event(new UserSessionChange( 'User logged in',  'success'));
});





