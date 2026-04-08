<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // badge_earned, level_up, booking_approved, booking_rejected, event_completed, place_unlocked, xp_earned, follow, comment, reaction, admin
            $table->string('title');
            $table->text('body');
            $table->json('data')->nullable(); // extra payload (badge_id, event_id, post_id, etc.)
            $table->boolean('is_read')->default(false);
            $table->timestamps();
            $table->index(['user_id', 'is_read', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_notifications');
    }
};
