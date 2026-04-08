<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('points_history')) {
            Schema::create('points_history', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->integer('amount');
                $table->string('source');
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
        }

        Schema::table('badges', function (Blueprint $table) {
            if (!Schema::hasColumn('badges', 'rarity')) $table->string('rarity')->default('common')->after('category');
            if (!Schema::hasColumn('badges', 'is_repeatable')) $table->boolean('is_repeatable')->default(false)->after('is_active');
            if (!Schema::hasColumn('badges', 'max_claims')) $table->unsignedInteger('max_claims')->nullable()->after('is_repeatable');
        });

        if (Schema::hasColumn('user_badges', 'is_viewed') && !Schema::hasColumn('user_badges', 'claim_count')) {
            Schema::table('user_badges', function (Blueprint $table) {
                $table->unsignedInteger('claim_count')->default(1)->after('is_viewed');
            });
        }

        if (!Schema::hasColumn('places', 'points_reward')) {
            Schema::table('places', function (Blueprint $table) {
                $table->unsignedInteger('points_reward')->default(0)->after('xp_reward');
            });
        }

        if (!Schema::hasTable('app_settings')) {
            Schema::create('app_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->string('group')->default('general');
                $table->string('description')->nullable();
                $table->timestamps();
            });
        }

        $settings = [
            ['key'=>'points_per_level_up','value'=>'10','group'=>'points','description'=>'Points earned per level up'],
            ['key'=>'points_event_easy','value'=>'5','group'=>'points','description'=>'Points for completing easy event'],
            ['key'=>'points_event_moderate','value'=>'10','group'=>'points','description'=>'Points for completing moderate event'],
            ['key'=>'points_event_hard','value'=>'20','group'=>'points','description'=>'Points for completing hard event'],
            ['key'=>'points_event_extreme','value'=>'30','group'=>'points','description'=>'Points for completing extreme event'],
            ['key'=>'welcome_badge_id','value'=>'1','group'=>'general','description'=>'Badge ID to award on registration'],
            ['key'=>'welcome_xp','value'=>'10','group'=>'xp','description'=>'XP awarded on registration'],
        ];
        foreach ($settings as $s) {
            \DB::table('app_settings')->insertOrIgnore(array_merge($s, ['created_at'=>now(),'updated_at'=>now()]));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('app_settings');
        Schema::dropIfExists('points_history');
        if (Schema::hasColumn('badges', 'rarity')) {
            Schema::table('badges', function (Blueprint $table) {
                $table->dropColumn(['rarity', 'is_repeatable', 'max_claims']);
            });
        }
        if (Schema::hasColumn('user_badges', 'claim_count')) {
            Schema::table('user_badges', function (Blueprint $table) {
                $table->dropColumn('claim_count');
            });
        }
        if (Schema::hasColumn('places', 'points_reward')) {
            Schema::table('places', function (Blueprint $table) {
                $table->dropColumn('points_reward');
            });
        }
    }
};
