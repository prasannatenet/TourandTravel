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
        Schema::table('tour_packages', function (Blueprint $table) {
            $table->string('category', 80)->nullable()->after('name');
            $table->string('tour_type', 20)->default('group')->after('category');
            $table->decimal('child_price', 12, 2)->nullable()->after('base_price');
            $table->decimal('infant_price', 12, 2)->nullable()->after('child_price');
            $table->decimal('discount_percentage', 5, 2)->default(0)->after('infant_price');
            $table->decimal('tax_percentage', 5, 2)->default(0)->after('discount_percentage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tour_packages', function (Blueprint $table) {
            $table->dropColumn(['category', 'tour_type', 'child_price', 'infant_price', 'discount_percentage', 'tax_percentage']);
        });
    }
};
