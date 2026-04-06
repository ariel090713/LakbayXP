<?php

use Illuminate\Database\Migrations\Migration;

// No schema change needed — EventStatus enum already handles string values.
// We just need to add 'pending_review' to the PHP enum.
return new class extends Migration
{
    public function up(): void {}
    public function down(): void {}
};
