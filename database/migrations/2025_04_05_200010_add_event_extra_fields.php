<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->date('end_date')->nullable()->after('event_date');
            $table->string('meeting_time', 50)->nullable()->after('meeting_place');
            $table->string('difficulty', 20)->nullable()->after('auto_approve_bookings');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['end_date', 'meeting_time', 'difficulty']);
        });
    }
};
