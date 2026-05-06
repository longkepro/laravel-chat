<?php

use App\Models\Conversation;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.{conversationId}', function ($user, $conversationId) {
    $isMember = Conversation::where('id', $conversationId)
        ->where(function ($q) use ($user) {
            $q->where('user1_id', $user->id)
                ->orWhere('user2_id', $user->id);
        })->exists();

    return $isMember
        ? ['id' => $user->id, 'username' => $user->username, 'avatar' => $user->avatar]
        : false;
});

Broadcast::channel('notifications.{receiverID}', function ($user, $receiverId) {
    return (int) $user->id === (int) $receiverId;
});

Broadcast::channel('online', function ($user) {
    return [
        'id' => $user->id,
        'name' => $user->profile_name ?? $user->username,
        'avatar' => $user->avatar,
    ];
});

Broadcast::channel('typing.{conversationId}', function ($user, $conversationId) {
    return Conversation::where('id', $conversationId)
        ->where(function ($q) use ($user) {
            $q->where('user1_id', $user->id)
                ->orWhere('user2_id', $user->id);
        })->exists();
});
