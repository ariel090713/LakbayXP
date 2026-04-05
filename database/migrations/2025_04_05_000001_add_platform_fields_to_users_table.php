<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->unique()->after('name');
            $table->text('bio')->nullable()->after('username');
            $table->string('avatar_path')->nullable()->after('bio');
            $table->string('role')->default('user')->after('avatar_path');
            $table->string('firebase_uid')->nullable()->unique()->after('role');
            $table->string('google_id')->nullable()->after('firebase_uid');
            $table->string('explorer_level')->default('beginner_explorer')->after('google_id');
            $table->boolean('is_verified_organizer')->default(false)->after('explorer_level');
            $table->string('fcm_token')->nullable()->after('is_verified_organizer');

            // Firebase users may not have a password
            $table->string('password')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'username',
                'bio',
                'avatar_path',
                'role',
                'firebase_uid',
                'google_id',
                'explorer_level',
                'is_verified_organizer',
                'fcm_token',
            ]);

            $table->string('password')->nullable(false)->change();
        });
    }
};
