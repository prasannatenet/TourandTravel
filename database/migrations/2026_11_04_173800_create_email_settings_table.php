<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('host');
            $table->unsignedSmallInteger('port');
            $table->string('encryption', 10);
            $table->string('username')->nullable();
            $table->text('password')->nullable();
            $table->string('from_email');
            $table->string('from_name');
            $table->string('test_recipient_email')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_settings');
    }
};
