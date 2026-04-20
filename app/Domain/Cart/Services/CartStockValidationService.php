<?php

namespace App\Domain\Cart\Services;

use App\Domain\Cart\DTOs\StockValidationResultDTO;
use App\Domain\ProductCatalog\Enums\ProductStatus;
use App\Domain\ProductCatalog\Models\Product;

class CartStockValidationService
{
    public function validate(Product $product, int $requestedQuantity): StockValidationResultDTO
    {
        $requestedQuantity = max(1, $requestedQuantity);

        if ($product->status !== ProductStatus::Active) {
            return StockValidationResultDTO::denied('This product is not available for purchase.');
        }

        if ($product->stock <= 0) {
            return StockValidationResultDTO::denied('This item is out of stock.');
        }

        if ($requestedQuantity > $product->stock) {
            return StockValidationResultDTO::warned(
                (int) $product->stock,
                "Only {$product->stock} left in stock. Quantity was adjusted."
            );
        }

        return StockValidationResultDTO::allowed($requestedQuantity);
    }
}
