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
        Schema::table('hotels', function (Blueprint $table) {
            $table->string('hotel_code')->nullable()->unique();
            $table->string('username', 50)->nullable()->unique();
            $table->string('password')->nullable();
        });

        DB::table('hotels')->select('id')->orderBy('id')->chunkById(500, function ($hotels): void {
            foreach ($hotels as $hotel) {
                DB::table('hotels')
                    ->where('id', $hotel->id)
                    ->update(['hotel_code' => 'HTL-'.str_pad((string) $hotel->id, 6, '0', STR_PAD_LEFT)]);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->dropUnique('hotels_hotel_code_unique');
            $table->dropUnique('hotels_username_unique');
            $table->dropColumn(['hotel_code', 'username', 'password']);
        });
    }
};
