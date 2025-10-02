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
    Schema::create('promotions', function (Blueprint $table) {
    $table->id();
    $table->string('code_promo')->unique();
    $table->text('promo_description')->nullable();
    $table->enum('promo_discount_type', ['percent','fixed']);
    $table->decimal('promo_discount_value', 10, 2);
    $table->date('promo_start_date')->nullable();
    $table->date('promo_end_date')->nullable();
    $table->unsignedInteger('promo_usage_limit')->nullable(); // total uses
    $table->boolean('promo_is_active')->default(true);
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
