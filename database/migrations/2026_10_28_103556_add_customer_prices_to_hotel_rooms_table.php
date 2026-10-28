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
        Schema::table('hotel_rooms', function (Blueprint $table) {
            $table->decimal('customer_adult_price', 10, 2)->nullable();
            $table->decimal('customer_child_price', 10, 2)->nullable();
            $table->decimal('customer_extra_bed_price', 10, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hotel_rooms', function (Blueprint $table) {
            $table->dropColumn([
                'customer_adult_price',
                'customer_child_price',
                'customer_extra_bed_price',
            ]);
        });
    }
};
