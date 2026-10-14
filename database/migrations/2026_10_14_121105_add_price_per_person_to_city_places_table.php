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
        Schema::table('city_places', function (Blueprint $table) {
            $table->decimal('price_per_person', 10, 2)->default(0)->after('best_time_to_visit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('city_places', function (Blueprint $table) {
            $table->dropColumn('price_per_person');
        });
    }
};
