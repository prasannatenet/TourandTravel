<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('hotel_rooms', function (Blueprint $table) {
            $table->decimal('adult_cost', 10, 2)->nullable();
            $table->decimal('child_cost', 10, 2)->nullable();
            $table->decimal('extra_bed_cost', 10, 2)->nullable();
        });

        DB::table('hotel_rooms')
            ->whereNotNull('price')
            ->update(['adult_cost' => DB::raw('price')]);

        Schema::table('hotel_rooms', function (Blueprint $table) {
            $table->dropColumn('price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::table('hotel_rooms')->whereNotNull('child_cost')->orWhereNotNull('extra_bed_cost')->exists()) {
            throw new RuntimeException('Room rates cannot be rolled back while child or extra-bed prices are set.');
        }

        Schema::table('hotel_rooms', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->nullable();
        });

        DB::table('hotel_rooms')
            ->whereNotNull('adult_cost')
            ->update(['price' => DB::raw('adult_cost')]);

        Schema::table('hotel_rooms', function (Blueprint $table) {
            $table->dropColumn(['adult_cost', 'child_cost', 'extra_bed_cost']);
        });
    }
};
