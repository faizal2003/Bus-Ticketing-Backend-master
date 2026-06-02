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
        $indexes = Schema::getIndexes('bookings');
        $hasIndex = false;
        foreach ($indexes as $index) {
            if ($index['name'] === 'bookings_ticket_status_unique') {
                $hasIndex = true;
                break;
            }
        }

        if ($hasIndex) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropUnique('bookings_ticket_status_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 
    }
};
