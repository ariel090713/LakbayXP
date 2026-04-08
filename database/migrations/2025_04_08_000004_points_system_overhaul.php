<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Points history (like xp_history but for points)
        Schema::create('points_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('amount'); // positive = earned, negative = spent
            $table->string('source'); // badge, event, level_up, promo, admin, reward_redeem
            $table->string('description')->nullable();
            $table->foreignId('badge_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('event_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('reward_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('granted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('balance_after');
            $table->timestamps();
            $table->index(['user_id', 'created_at']);
            $table->index(['user_id', 'source']);
        });

        // Badge: add rarity, is_repeatable, max_claims
        Schema::table('badges', function (Blueprint $table) {
            $table->string('rarity')->default('common')->after('category'); // common, rare, epic, legendary
            $table->boolean('is_repeatable')->default(false)->after('is_active');
            $table->unsignedInteger('max_claims')->nullable()->after('is_repeatable'); // null = unlimited if repeatable
        });

        // user_badges: add claim count
        Schema::table('user_badges', function (Blueprint $table) {
            $table->unsignedInteger('claim_count')->default(1)->after('is_viewed');
        });

        // Places: add points_reward
        Schema::table('places', function (Blueprint $table) {
            $table->unsignedInteger('points_reward')->default(0)->after('xp_reward');
        });

        // Admin settings table
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group')->default('general'); // general, points, xp
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Seed default settings
        \DB::table('app_settings')->insert([
            ['key' => 'points_per_level_up', 'value' => '10', 'group' => 'points', 'description' => 'Points earned per level up', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'points_event_easy', 'value' => '5', 'group' => 'points', 'description' => 'Points for completing easy event', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'points_event_moderate', 'value' => '10', 'group' => 'points', 'description' => 'Points for completing moderate event', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'points_event_hard', 'value' => '20', 'group' => 'points', 'description' => 'Points for completing hard event', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'points_event_extreme', 'value' => '30', 'group' => 'points', 'description' => 'Points for completing extreme event', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'welcome_badge_id', 'value' => '1', 'group' => 'general', 'description' => 'Badge ID to award on registration', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'welcome_xp', 'value' => '10', 'group' => 'xp', 'description' => 'XP awarded on registration', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('app_settings');
        Schema::dropIfExists('points_history');
        Schema::table('badges', function (Blueprint $table) {
            $table->dropColumn(['rarity', 'is_repeatable', 'max_claims']);
        });
        Schema::table('user_badges', function (Blueprint $table) {
            $table->dropColumn('claim_count');
        });
        Schema::table('places', function (Blueprint $table) {
            $table->dropColumn('points_reward');
        });
    }
};
