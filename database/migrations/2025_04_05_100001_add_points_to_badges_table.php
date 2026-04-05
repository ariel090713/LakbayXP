<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('badges', function (Blueprint $table) {
            $table->unsignedInteger('points')->default(0)->after('is_active');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('total_points')->default(0)->after('fcm_token');
            $table->unsignedInteger('available_points')->default(0)->after('total_points');
        });
    }

    public function down(): void
    {
        Schema::table('badges', function (Blueprint $table) {
            $table->dropColumn('points');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['total_points', 'available_points']);
        });
    }
};
