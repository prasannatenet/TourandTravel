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
        Schema::create('package_inclusion_package_plan_item', function (Blueprint $table) {
            $table->foreignId('package_inclusion_id')->constrained()->cascadeOnDelete();
            $table->foreignId('package_plan_item_id')->constrained()->cascadeOnDelete();
            $table->primary(['package_inclusion_id', 'package_plan_item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_inclusion_package_plan_item');
    }
};
