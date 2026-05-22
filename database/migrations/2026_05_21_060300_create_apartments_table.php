<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('apartments', function (Blueprint $table) {
            $table->id();
            $table->string('block');
            $table->string('number');
            $table->timestamps();

            $table->unique(['block', 'number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apartments');
    }
};
