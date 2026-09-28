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
        Schema::create('coffee_shops', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('address');
            $table->string('city')->index();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('phone')->nullable();
            $table->string('instagram')->nullable();
            $table->string('image_url')->nullable();

            // Pricing
            $table->unsignedInteger('price_min')->default(15000);
            $table->unsignedInteger('price_max')->default(50000);
            $table->string('price_range')->default('$$');

            // Rating & Review
            $table->decimal('rating', 3, 2)->default(0.00)->index();
            $table->unsignedInteger('review_count')->default(0);

            // Operational Hours
            $table->time('opening_time')->nullable();
            $table->time('closing_time')->nullable();

            // Facilities & Workspace attributes
            $table->boolean('has_wifi')->default(false)->index();
            $table->boolean('has_power_outlets')->default(false)->index();
            $table->boolean('is_ac')->default(false)->index();
            $table->boolean('is_outdoor')->default(false)->index();
            $table->boolean('is_smoking_area')->default(false);
            $table->boolean('is_work_friendly')->default(false)->index();
            $table->boolean('has_prayer_room')->default(false);

            // Atmosphere & Vibe
            $table->string('ambiance')->default('cozy')->index();
            $table->unsignedSmallInteger('wifi_speed_mbps')->nullable();
            $table->string('noise_level')->default('moderate');

            // Status
            $table->boolean('is_active')->default(true)->index();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coffee_shops');
    }
};
