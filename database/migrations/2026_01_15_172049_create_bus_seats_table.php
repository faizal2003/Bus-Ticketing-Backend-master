<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bus_seats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bus_id')
                  ->constrained('buses')
                  ->cascadeOnDelete();

            $table->string('seat_number', 10);
            $table->string('seat_class', 50)->default('regular');
            $table->boolean('is_available')->default(true);

            $table->unique(['bus_id', 'seat_number']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bus_seats');
    }
};
