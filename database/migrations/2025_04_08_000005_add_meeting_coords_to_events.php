<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (!Schema::hasColumn('events', 'meeting_lat')) {
                $table->decimal('meeting_lat', 10, 7)->nullable()->after('meeting_time');
            }
            if (!Schema::hasColumn('events', 'meeting_lng')) {
                $table->decimal('meeting_lng', 10, 7)->nullable()->after('meeting_lat');
            }
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['meeting_lat', 'meeting_lng']);
        });
    }
};
