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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organizer_id')->constrained('users');
            $table->foreignId('place_id')->constrained('places');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('category');
            $table->date('event_date');
            $table->string('meeting_place')->nullable();
            $table->decimal('fee', 10, 2)->default(0);
            $table->unsignedInteger('max_slots');
            $table->json('requirements')->nullable();
            $table->string('status')->default('draft');
            $table->boolean('auto_approve_bookings')->default(false);
            $table->timestamps();
            $table->index(['status', 'event_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
