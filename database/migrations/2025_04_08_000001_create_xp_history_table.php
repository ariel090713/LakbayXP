<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('xp_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('amount'); // can be negative for deductions
            $table->string('source'); // place_unlock, badge, event, promo, admin, welcome, referral
            $table->string('category')->nullable(); // mountain, beach, etc. (from place category)
            $table->string('description')->nullable();
            $table->foreignId('place_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('badge_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('event_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('granted_by')->nullable()->constrained('users')->nullOnDelete(); // admin who granted
            $table->unsignedBigInteger('balance_after'); // user's total XP after this entry
            $table->timestamps();
            $table->index(['user_id', 'created_at']);
            $table->index(['user_id', 'category']);
            $table->index(['user_id', 'source']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('xp_history');
    }
};
