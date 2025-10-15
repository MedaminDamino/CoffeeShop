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
        Schema::dropIfExists('order_items');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate the order_items table if needed
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->jsonb('products'); // array of {product_id, ord_quantity, ord_price}
            $table->decimal('ord_price', 10, 2); // total price for this item group
            $table->jsonb('meta')->nullable(); // e.g., size, extra shots
            $table->timestamps();
        });
    }
};
