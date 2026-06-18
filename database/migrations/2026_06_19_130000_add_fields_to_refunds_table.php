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
        Schema::table('refunds', function (Blueprint $table) {
            $table->text('admin_notes')->nullable()->after('reason');
            $table->timestamp('processed_at')->nullable()->after('status');
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null')->after('processed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('refunds', function (Blueprint $table) {
            $table->dropForeign(['processed_by']);
            $table->dropColumn(['admin_notes', 'processed_at', 'processed_by']);
        });
    }
};
