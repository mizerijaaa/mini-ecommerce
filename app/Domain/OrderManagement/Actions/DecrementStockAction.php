<?php

namespace App\Domain\OrderManagement\Actions;

use App\Domain\OrderManagement\Exceptions\CheckoutException;
use App\Domain\ProductCatalog\Models\Product;

class DecrementStockAction
{
    public function execute(Product $product, int $quantity): void
    {
        $quantity = max(1, $quantity);

        $updated = Product::query()
            ->whereKey($product->id)
            ->where('stock', '>=', $quantity)
            ->decrement('stock', $quantity);

        if ($updated !== 1) {
            throw new CheckoutException("Insufficient stock for {$product->name}.");
        }
    }
}
