<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_places', function (Blueprint $table) {
            // Make place_id nullable (null = custom place not in system)
            $table->foreignId('place_id')->nullable()->change();
            // Custom place name (used when place_id is null)
            $table->string('custom_place_name')->nullable()->after('place_id');
            $table->string('custom_place_location')->nullable()->after('custom_place_name');
        });
    }

    public function down(): void
    {
        Schema::table('event_places', function (Blueprint $table) {
            $table->dropColumn(['custom_place_name', 'custom_place_location']);
        });
    }
};
