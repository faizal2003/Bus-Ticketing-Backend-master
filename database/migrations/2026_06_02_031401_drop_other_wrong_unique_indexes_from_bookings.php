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
        $dropBoardingStatus = false;
        $dropPaymentMethod = false;

        foreach ($indexes as $index) {
            if ($index['name'] === 'bookings_boarding_status_unique') {
                $dropBoardingStatus = true;
            }
            if ($index['name'] === 'bookings_payment_method_unique') {
                $dropPaymentMethod = true;
            }
        }

        Schema::table('bookings', function (Blueprint $table) use ($dropBoardingStatus, $dropPaymentMethod) {
            if ($dropBoardingStatus) {
                $table->dropUnique('bookings_boarding_status_unique');
            }
            if ($dropPaymentMethod) {
                $table->dropUnique('bookings_payment_method_unique');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 
    }
};
