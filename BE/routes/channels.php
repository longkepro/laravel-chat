<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.{conversationId}', function ($user, $conversationId) {
    $isMember = Conversation::where('id', $conversationId)
        ->where(function ($q) use ($user) {
            $q->where('user1_id', $user->id)
              ->orWhere('user2_id', $user->id);
        })->exists();

    return $isMember ? ['id' => $user->id, 'username' => $user->username, 'avatar' => $user->avatar] : false;
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
    return Conversation::where('id', $conversationId)
        ->where(function ($q) use ($user) {
            $q->where('user1_id', $user->id)
              ->orWhere('user2_id', $user->id);
        })->exists();
});
