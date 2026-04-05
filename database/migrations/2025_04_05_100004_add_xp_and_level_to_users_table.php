<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('xp')->default(0)->after('available_points');
            $table->unsignedInteger('level')->default(1)->after('xp');
        });

        Schema::table('badges', function (Blueprint $table) {
            $table->unsignedInteger('xp_reward')->default(0)->after('points');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['xp', 'level']);
        });

        Schema::table('badges', function (Blueprint $table) {
            $table->dropColumn('xp_reward');
        });
    }
};
