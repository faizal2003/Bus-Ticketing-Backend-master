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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['super_admin', 'admin', 'kondektur', 'penumpang'])->default('penumpang')->change();
        });

        Schema::table('buses', function (Blueprint $table) {
            $table->enum('bus_type', ['regular', 'executive', 'vip', 'super', 'reguler', 'premium', 'ekonomi', 'bisnis'])->default('regular')->change();
            $table->enum('status', ['active', 'inactive', 'maintenance'])->default('active')->change();
            $table->unsignedInteger('total_seats')->change();
        });

        Schema::table('bus_schedules', function (Blueprint $table) {
            $table->enum('status', ['active', 'inactive', 'completed', 'cancelled'])->default('active')->change();
            $table->decimal('price_per_seat', 10, 2)->unsigned()->change();
            $table->unsignedInteger('available_seats')->change();
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->enum('booking_status', ['pending', 'confirmed', 'cancelled', 'completed'])->default('pending')->change();
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'expired', 'refunded'])->default('pending')->change();
            $table->enum('ticket_status', ['active', 'used', 'cancelled', 'expired'])->nullable()->default('active')->change();
            $table->enum('boarding_status', ['pending', 'boarded', 'missed'])->nullable()->default('pending')->change();
            $table->unsignedInteger('total_passengers')->change();
            $table->decimal('total_price', 12, 2)->unsigned()->change();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->enum('status', ['pending', 'paid', 'success', 'failed', 'expired', 'settlement', 'deny', 'cancel', 'expire'])->default('pending')->change();
            $table->decimal('amount', 12, 2)->unsigned()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('penumpang')->change();
        });

        Schema::table('buses', function (Blueprint $table) {
            $table->string('bus_type', 50)->default('Regular')->change();
            $table->string('status', 20)->default('active')->change();
            $table->integer('total_seats')->change();
        });

        Schema::table('bus_schedules', function (Blueprint $table) {
            $table->string('status', 20)->default('active')->change();
            $table->decimal('price_per_seat', 10, 2)->change();
            $table->integer('available_seats')->change();
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->string('booking_status', 30)->default('pending')->change();
            $table->string('payment_status', 30)->default('pending')->change();
            $table->string('ticket_status', 30)->nullable()->default('active')->change();
            $table->string('boarding_status', 30)->nullable()->default('pending')->change();
            $table->integer('total_passengers')->change();
            $table->decimal('total_price', 12, 2)->change();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->string('status', 30)->default('pending')->change();
            $table->decimal('amount', 12, 2)->change();
        });
    }
};
