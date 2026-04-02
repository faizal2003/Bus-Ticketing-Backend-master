<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bus_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bus_id')
                  ->constrained('buses')
                  ->cascadeOnDelete();

            $table->string('departure_city', 100);
            $table->string('arrival_city', 100);
            $table->timestamp('departure_time');
            $table->timestamp('arrival_time');
            $table->decimal('price_per_seat', 10, 2);
            $table->integer('available_seats');
            $table->string('status', 20)->default('active');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bus_schedules');
    }
};
