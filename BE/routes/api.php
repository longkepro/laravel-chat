<?php

use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\ChatController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('login', [LoginController::class, 'login']);
    Route::post('register', [RegisterController::class, 'register']);
    Route::get('google/redirect', [SocialAuthController::class, 'redirectToGoogle'])->name('api.google.redirect');
    Route::get('google/callback', [SocialAuthController::class, 'handleGoogleCallback'])->name('api.google.callback');
    Route::get('facebook/redirect', [SocialAuthController::class, 'redirectToFaceBook'])->name('api.facebook.redirect');
    Route::get('facebook/callback', [SocialAuthController::class, 'handleFaceBookCallback'])->name('api.facebook.callback');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('user', fn (Request $request) => $request->user());
    Route::post('auth/logout', [LoginController::class, 'destroy']);
    Route::get('conversations', [ChatController::class, 'getConversationList']);
    Route::get('conversations/{conversation}/messages', [ChatController::class, 'getMessages']);
    Route::post('conversations/sendmessages', [ChatController::class, 'sendMessage']);
    Route::post('conversations/create', [ChatController::class, 'createConversation']);
});
