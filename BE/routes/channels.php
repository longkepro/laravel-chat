<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.{conversationId}', function ($conversation, $conversationId) {
    return (int) $conversation->id === (int) $conversationId;
});

Broadcast::channel('notifications.{receiverID}', function ($user, $receiverId) {
    return (int) $user->id === (int) $receiverId;
});

Broadcast::channel('online', function ($user) {
    if (Auth::check()) {
        //Mảng trả về ở đây sẽ là dữ liệu mà Frontend của người khác nhận được trong sự kiện 'joining' và 'here'.
        return [
            'id' => $user->id,
            'name' => $user->name,
            'avatar' => $user->avatar_url,
        ];
    }
    // Nếu trả về null hoặc false -> Từ chối kết nối (403 Forbidden)
});
Broadcast::channel('typing.{conversationId}', function ($user, $conversationId) {
    return (int) $user->id === (int) $conversationId;
});
