<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('organizer_type')->nullable()->after('is_verified_organizer'); // solo, agency, organization
            $table->string('organization_name')->nullable()->after('organizer_type');
            $table->string('phone')->nullable()->after('organization_name');
            $table->text('organizer_bio')->nullable()->after('phone');
            $table->json('social_links')->nullable()->after('organizer_bio'); // {facebook, instagram, website}
            $table->json('specialties')->nullable()->after('social_links'); // [mountain, beach, island...]
            $table->boolean('onboarding_completed')->default(false)->after('specialties');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'organizer_type', 'organization_name', 'phone',
                'organizer_bio', 'social_links', 'specialties', 'onboarding_completed',
            ]);
        });
    }
};
