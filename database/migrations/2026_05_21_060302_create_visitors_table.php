<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitors', function (Blueprint $table) {
            $table->id();
            $table->string('visitor_name');
            $table->string('phone_number');
            $table->foreignId('apartment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('resident_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reason');
            $table->string('photo_path')->nullable();
            $table->string('status')->default('pending');
            $table->timestamp('entry_time')->nullable();
            $table->timestamp('exit_time')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitors');
    }
};
