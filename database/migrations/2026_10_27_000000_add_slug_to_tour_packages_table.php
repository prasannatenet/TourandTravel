<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tour_packages', function (Blueprint $table): void {
            $table->string('slug')->unique()->after('image');
            $table->index('slug');
        });
    }

    public function down(): void
    {
        Schema::table('tour_packages', function (Blueprint $table): void {
            $table->dropIndex('tour_packages_slug_index');
            $table->dropColumn('slug');
        });
    }
};
