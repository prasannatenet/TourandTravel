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
        Schema::table('package_inclusion_package', function (Blueprint $table) {
            $table->boolean('customized')->default(false)->after('tour_package_id');
            $table->string('custom_name', 120)->nullable();
            $table->string('custom_place_from', 150)->nullable();
            $table->string('custom_place_to', 150)->nullable();
            $table->text('custom_description')->nullable();
            $table->time('custom_scheduled_time')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('package_inclusion_package', function (Blueprint $table) {
            $table->dropColumn([
                'customized',
                'custom_name',
                'custom_place_from',
                'custom_place_to',
                'custom_description',
                'custom_scheduled_time',
            ]);
        });
    }
};
