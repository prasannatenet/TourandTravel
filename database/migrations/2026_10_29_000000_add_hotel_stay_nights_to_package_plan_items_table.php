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
        Schema::table('package_plan_items', function (Blueprint $table): void {
            $table->unsignedTinyInteger('nights')
                ->nullable()
                ->after('day_number')
                ->comment('Number of nights stayed at the hotel (applicable for hotel check-in plan items).');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('package_plan_items', function (Blueprint $table): void {
            $table->dropColumn('nights');
        });
    }
};
