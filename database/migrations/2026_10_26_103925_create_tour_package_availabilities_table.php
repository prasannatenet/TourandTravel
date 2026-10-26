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
        if (Schema::hasTable('tour_package_availabilities')) {
            Schema::table('tour_package_availabilities', function (Blueprint $table) {
                $table->unique(['tour_package_id', 'departure_date'], 'package_departure_unique');
            });

            return;
        }

        Schema::create('tour_package_availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_package_id')->constrained()->cascadeOnDelete();
            $table->date('departure_date');
            $table->unsignedSmallInteger('seat_limit');
            $table->decimal('adult_price_override', 12, 2)->nullable();
            $table->timestamps();
            $table->unique(['tour_package_id', 'departure_date'], 'package_departure_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tour_package_availabilities');
    }
};
