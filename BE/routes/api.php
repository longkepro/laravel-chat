<?php

use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\ChatController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\profileController;

Route::prefix('auth')->middleware('web')->group(function () {
    Route::post('login', [LoginController::class, 'login']);
    Route::post('register', [RegisterController::class, 'register']);
    Route::get('google/redirect', [SocialAuthController::class, 'redirectToGoogle'])->name('api.google.redirect');
    Route::get('facebook/redirect', [SocialAuthController::class, 'redirectToFaceBook'])->name('api.facebook.redirect');
});
Route::prefix('login')->middleware('web')->group(function () {
    Route::get('facebook/callback', [SocialAuthController::class, 'handleFaceBookCallback'])->name('api.facebook.callback');
    Route::get('google/callback', [SocialAuthController::class, 'handleGoogleCallback'])->name('api.google.callback');
});

Route::middleware('auth:sanctum')->group(function () {
    //Route::get('user', fn (Request $request) => $request->user())->name('user');
    Route::post('auth/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('conversations', [ChatController::class, 'getConversationList'])->name('conversations');

    Route::get('conversations/{conversationID}/olderMessages/{MessageID}', [ChatController::class, 'getOlderMessages'])->name('conversations.oldermessages');

    Route::get('conversations/{conversationID}/newerMessages/{MessageID}', [ChatController::class, 'getNewerMessages'])->name('conversations.newermessages');

    Route::get('conversations/{conversationID}/fetchSearchMessages/{MessageID}', [ChatController::class, 'fetchSearchedMessages'])->name('conversations.fetchsearchedmessages');

    Route::post('conversations/sendmessages', [ChatController::class, 'sendMessage'])->name('conversations.sendmessages');

    Route::post('conversations/create', [ChatController::class, 'createConversation'])->name('conversations.create');

    Route::get('profile/authUser', [profileController::class, 'getAuthUser'])->name('profile.getauthuser');

    Route::get('profile/{userID}', [profileController::class, 'getProfile'])->name('profile.get');

    Route::post('profile/editProfile', [profileController::class, 'editProfile'])->name('profile.editprofile');

    Route::post('profile/updatePassword', [profileController::class, 'updatePassword'])->name('profile.updatePassword');

    Route::get('search/users', [profileController::class, 'searchUsers'])->name('users.search');

    Route::get('search/messages', [ChatController::class, 'searchMessages'])->name('messages.search');

});
