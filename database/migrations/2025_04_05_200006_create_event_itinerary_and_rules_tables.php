<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Event can have multiple places (itinerary)
        Schema::create('event_places', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('place_id')->constrained('places')->cascadeOnDelete();
            $table->unsignedInteger('day_number')->default(1); // Day 1, Day 2, etc.
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('activity')->nullable(); // e.g. "Summit climb", "Beach cleanup"
            $table->string('time_slot')->nullable(); // e.g. "6:00 AM - 12:00 PM"
            $table->text('notes')->nullable();
        });

        // Event rules (dynamic, admin/organizer defined)
        Schema::create('event_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->string('rule_type'); // requirement, inclusion, exclusion, reminder, policy
            $table->text('content');
            $table->unsignedInteger('sort_order')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_rules');
        Schema::dropIfExists('event_places');
    }
};
