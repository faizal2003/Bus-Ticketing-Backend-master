<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Drop unique constraint if it exists
            // Laravel's default name for this unique index would be 'bookings_payment_method_unique'
            try {
                $table->dropUnique(['payment_method']);
            } catch (\Exception $e) {
                // If it doesn't exist, just ignore
            }
        });
    }

    public function down(): void
    {
        // No need to restore the unique constraint as it was likely a bug
    }
};
