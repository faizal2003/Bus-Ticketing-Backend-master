<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buses', function (Blueprint $table) {
            $table->id();
            $table->string('bus_number', 50)->unique();
            $table->string('bus_name', 100);
            $table->string('plate_number', 20)->unique();
            $table->string('bus_type', 50)->default('Regular');
            $table->integer('total_seats');
            $table->json('facilities')->nullable();
            $table->string('status', 20)->default('active');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buses');
    }
};
