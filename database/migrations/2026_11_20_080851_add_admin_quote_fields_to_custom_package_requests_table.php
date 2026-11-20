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
        Schema::table('custom_package_requests', function (Blueprint $table): void {
            $table->decimal('admin_quote', 12, 2)->nullable()->after('admin_response');
            $table->text('admin_cost_notes')->nullable()->after('admin_quote');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('custom_package_requests', function (Blueprint $table): void {
            $table->dropColumn(['admin_quote', 'admin_cost_notes']);
        });
    }
};
