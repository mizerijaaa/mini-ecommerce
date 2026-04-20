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
            $table->ulid('id')->primary();

            $table->foreignUlid('vendor_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('name')->index();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->index();
            $table->unsignedInteger('stock')->default(0)->index();
            $table->string('image_url')->nullable();
            $table->enum('status', ['draft', 'active', 'archived'])->default('draft')->index();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['vendor_id', 'status']);
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
