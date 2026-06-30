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
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone');
            $table->string('license_number')->nullable();
            $table->string('status', 20)->default('active'); // active, inactive
            $table->timestamps();
        });

        Schema::table('buses', function (Blueprint $table) {
            $table->foreignId('driver_id')->nullable()->after('status')->constrained('drivers')->onDelete('set null');
            $table->foreignId('conductor_id')->nullable()->after('driver_id')->constrained('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('buses', function (Blueprint $table) {
            $table->dropForeign(['buses_driver_id_foreign']);
            $table->dropForeign(['buses_conductor_id_foreign']);
            $table->dropColumn(['driver_id', 'conductor_id']);
        });

        Schema::dropIfExists('drivers');
    }
};
