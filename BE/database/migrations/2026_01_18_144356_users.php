<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username', 50)->unique();
            $table->string('email', 150)->unique();
            $table->string('password');
            $table->string('google_id', 100)->nullable();
            $table->string('facebook_id', 100)->nullable();
            $table->string('avatar')->nullable();
            $table->string('profile_name', 100);
            $table->timestamps();
        });

        Schema::create('conversations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user1_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->foreignId('user2_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            // Tin nhắn cuối cùng chung
            $table->foreignId('last_message_id')
                  ->nullable()
                  ->nullOnDelete();

            // Tin nhắn cuối cùng mà user1 đã đọc / thấy
            $table->foreignId('last_message_id1')
                  ->nullable()
                  ->nullOnDelete();

            // Tin nhắn cuối cùng mà user2 đã đọc / thấy
            $table->foreignId('last_message_id2')
                  ->nullable()
                  ->nullOnDelete();

            $table->timestamps();

            $table->unique(['user1_id', 'user2_id']);
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();

            $table->foreignId('sender_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('receiver_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('conversation_id')
                ->constrained('conversations')
                ->cascadeOnDelete();

            $table->text('message');
            $table->longText('attachment')->nullable();
            $table->timestamps();

            $table->index('sender_id');
            $table->index('receiver_id');
        });



        DB::statement(
            'ALTER TABLE conversations
             ADD CONSTRAINT chk_user_order
             CHECK (user1_id < user2_id)'
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('conversations');
    }
};
