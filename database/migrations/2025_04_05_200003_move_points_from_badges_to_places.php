<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add points to places (for rewards redemption)
        Schema::table('places', function (Blueprint $table) {
            $table->unsignedInteger('points_reward')->default(0)->after('xp_reward');
        });
    }

    public function down(): void
    {
        Schema::table('places', function (Blueprint $table) {
            $table->dropColumn('points_reward');
        });
    }
};
