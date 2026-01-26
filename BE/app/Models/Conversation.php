<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable = ['user1_id', 'user2_id', 'last_message_id', 'last_message_id1', 'last_message_id2'];

    public function userOne()
    {
        return $this->belongsTo(User::class, 'user1_id');
    }

    public function userTwo()
    {
        return $this->belongsTo(User::class, 'user2_id');
    }
    public function lastMessage()
    {
        return $this->belongsTo(Message::class, 'last_message_id');
    }
    public function lastMessageUser1()
    {
        return $this->belongsTo(Message::class, 'last_message_id1');
    }
    public function lastMessageUser2()
    {
        return $this->belongsTo(Message::class, 'last_message_id2');
    }
}
