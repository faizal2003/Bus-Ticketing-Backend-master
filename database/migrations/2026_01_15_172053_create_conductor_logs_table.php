<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('conductor_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('conductor_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->string('action', 100);

            $table->foreignId('ticket_id')
                  ->nullable()
                  ->constrained('tickets')
                  ->nullOnDelete();

            $table->jsonb('details')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conductor_logs');
    }
};
