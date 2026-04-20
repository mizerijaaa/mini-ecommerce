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
        Schema::create('order_items', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('order_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignUlid('product_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignUlid('vendor_id')
                ->constrained()
                ->restrictOnDelete();

            $table->unsignedInteger('quantity');
            $table->decimal('price', 10, 2);
            $table->timestamps();

            $table->index(['order_id', 'vendor_id']);
            $table->index(['product_id', 'vendor_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};

