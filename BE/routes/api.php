<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ProfileController;
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
    Route::post('auth/logout', [LoginController::class, 'destroy'])->name('logout');
    Route::get('auth/me', [ProfileController::class, 'getAuthUser'])->name('auth.me');

    Route::get('conversations', [ChatController::class, 'getConversationList'])->name('conversations');
    Route::get('conversations/{conversationID}/latestMessages', [ChatController::class, 'getOlderMessages'])->name('conversations.latestmessages');
    Route::get('conversations/{conversationID}/olderMessages/{MessageID}', [ChatController::class, 'getOlderMessages'])->name('conversations.oldermessages');
    Route::get('conversations/{conversationID}/newerMessages/{MessageID}', [ChatController::class, 'getNewerMessages'])->name('conversations.newermessages');
    Route::get('conversations/{conversationID}/fetchSearchMessages/{MessageID}', [ChatController::class, 'fetchSearchedMessages'])->name('conversations.fetchsearchedmessages');
    Route::post('conversations/{conversationID}/read', [ChatController::class, 'markAsRead'])->name('conversations.read');
    Route::post('conversations/sendmessages', [ChatController::class, 'sendMessage'])->name('conversations.sendmessages');
    Route::post('conversations/create', [ChatController::class, 'createConversation'])->name('conversations.create');

    Route::get('profile/authUser', [ProfileController::class, 'getAuthUser'])->name('profile.getauthuser');
    Route::get('profile/{userID}', [ProfileController::class, 'getProfile'])->name('profile.get');
    Route::post('profile/editProfile', [ProfileController::class, 'editProfile'])->name('profile.editprofile');
    Route::post('profile/updatePassword', [ProfileController::class, 'updatePassword'])->name('profile.updatePassword');

    Route::get('search/users', [ProfileController::class, 'searchUsers'])->name('users.search');
    Route::get('search/messages', [ChatController::class, 'searchMessages'])->name('messages.search');
});
