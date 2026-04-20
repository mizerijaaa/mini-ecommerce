<?php

namespace App\Domain\Cart\Actions;

use App\Domain\Cart\DTOs\AddToCartDTO;
use App\Domain\Cart\DTOs\StockValidationResultDTO;
use App\Domain\Cart\Models\Cart;
use App\Domain\Cart\Models\CartItem;
use App\Domain\Cart\Services\CartStockValidationService;
use App\Domain\ProductCatalog\Models\Product;

class AddToCartAction
{
    public function __construct(
        private readonly CartStockValidationService $stockValidation,
    ) {}

    public function execute(AddToCartDTO $dto): StockValidationResultDTO
    {
        $product = Product::query()->active()->findOrFail($dto->productId);

        $cart = Cart::query()->firstOrCreate(['user_id' => $dto->userId]);

        $existing = CartItem::query()
            ->where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->first();

        $requested = $dto->quantity + ($existing?->quantity ?? 0);
        $validation = $this->stockValidation->validate($product, $requested);

        if (! $validation->allowed) {
            return $validation;
        }

        CartItem::query()->updateOrCreate(
            ['cart_id' => $cart->id, 'product_id' => $product->id],
            ['quantity' => $validation->allowedQuantity]
        );

        return $validation;
    }
}
