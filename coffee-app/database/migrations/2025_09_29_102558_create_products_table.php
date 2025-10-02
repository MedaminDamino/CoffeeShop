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
    Schema::create('products', function (Blueprint $table) {
    $table->id();
    $table->foreignId('category_id')->constrained()->cascadeOnDelete();
    $table->string('prod_name');
    $table->text('prod_description')->nullable();
    $table->decimal('prod_price', 10, 2);
    $table->string('prod_image_url')->nullable();
    $table->boolean('prod_is_active')->default(true);
    $table->jsonb('prod_meta')->nullable(); // flexible field e.g., calories/options
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
