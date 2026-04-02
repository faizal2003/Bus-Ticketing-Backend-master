<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Periksa dan perbaiki struktur tabel bookings
        Schema::table('bookings', function (Blueprint $table) {
            // 1. Pastikan foreign key schedule_id benar
            if (Schema::hasColumn('bookings', 'schedule_id')) {
                // Hapus foreign key lama jika ada
                $table->dropForeign(['schedule_id']);
            }

            // 2. Tambahkan kembali foreign key dengan constraint yang benar
            $table->foreign('schedule_id')
                  ->references('id')
                  ->on('bus_schedules')
                  ->onDelete('cascade');

            // 3. Tambahkan kolom yang hilang
            $columnsToAdd = [
                'payment_method' => ['type' => 'string', 'length' => 50, 'nullable' => true],
                'payment_date' => ['type' => 'timestamp', 'nullable' => true],
                'ticket_code' => ['type' => 'string', 'length' => 50, 'nullable' => true, 'unique' => true],
                'ticket_status' => ['type' => 'string', 'length' => 30, 'nullable' => true, 'default' => 'active'],
                'boarding_status' => ['type' => 'string', 'length' => 30, 'nullable' => true, 'default' => 'pending'],
            ];

            foreach ($columnsToAdd as $columnName => $config) {
                if (!Schema::hasColumn('bookings', $columnName)) {
                    if ($config['type'] === 'string') {
                        $table->string($columnName, $config['length'])
                              ->nullable($config['nullable'] ?? false)
                              ->unique($config['unique'] ?? false);
                    } elseif ($config['type'] === 'timestamp') {
                        $table->timestamp($columnName)->nullable($config['nullable'] ?? false);
                    }
                }
            }

            // 4. Tambahkan index untuk pencarian yang lebih cepat
            if (!Schema::hasIndex('bookings', ['booking_status', 'payment_status'])) {
                $table->index(['booking_status', 'payment_status']);
            }

            if (!Schema::hasIndex('bookings', ['schedule_id', 'created_at'])) {
                $table->index(['schedule_id', 'created_at']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Hapus index
            $table->dropIndex(['booking_status', 'payment_status']);
            $table->dropIndex(['schedule_id', 'created_at']);

            // Hapus kolom yang ditambahkan
            $table->dropColumn([
                'payment_method',
                'payment_date',
                'ticket_code',
                'ticket_status',
                'boarding_status'
            ]);
        });
    }
};
