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
    Schema::create('reservations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
    $table->foreignId('table_id')->constrained('tables')->cascadeOnDelete();
    $table->timestampTz('start_at');
    $table->timestampTz('end_at');
    $table->enum('res_status', ['pending','confirmed','canceled','completed'])->default('pending');
    $table->text('res_notes')->nullable();
    $table->timestamps();
    $table->index(['table_id','start_at']);
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
