<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('city_places', function (Blueprint $table): void {
            $table->decimal('price_child', 10, 2)->nullable()->after('price_per_person');
            $table->decimal('price_indian', 10, 2)->nullable()->after('price_child');
            $table->decimal('price_foreigner', 10, 2)->nullable()->after('price_indian');
            $table->decimal('price_student', 10, 2)->nullable()->after('price_foreigner');
        });

        DB::table('city_places')->update([
            'price_child' => DB::raw('price_per_person'),
            'price_indian' => DB::raw('price_per_person'),
            'price_foreigner' => DB::raw('price_per_person'),
            'price_student' => DB::raw('price_per_person'),
        ]);
    }

    public function down(): void
    {
        Schema::table('city_places', function (Blueprint $table): void {
            $table->dropColumn(['price_child', 'price_indian', 'price_foreigner', 'price_student']);
        });
    }
};
