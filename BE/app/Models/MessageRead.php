<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageRead extends Model
{
    protected $fillable = ['sender_id', 'recevier_id', 'last_read_message_id'];

    public function sender() {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver() {
        return $this->belongsTo(User::class, 'receiver_id');
    }
    public function lastReadMessage(){
        return $this->belongsTo(Message::class, 'last_read_message_id');
    }
}