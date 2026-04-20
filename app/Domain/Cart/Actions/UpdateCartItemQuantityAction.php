<?php

namespace App\Domain\Cart\Actions;

use App\Domain\Cart\DTOs\StockValidationResultDTO;
use App\Domain\Cart\DTOs\UpdateCartItemQuantityDTO;
use App\Domain\Cart\Models\CartItem;
use App\Domain\Cart\Services\CartStockValidationService;

class UpdateCartItemQuantityAction
{
    public function __construct(
        private readonly CartStockValidationService $stockValidation,
    ) {}

    public function execute(UpdateCartItemQuantityDTO $dto): StockValidationResultDTO
    {
        $item = CartItem::query()
            ->with('product')
            ->whereKey($dto->cartItemId)
            ->whereHas('cart', fn ($q) => $q->where('user_id', $dto->userId))
            ->firstOrFail();

        $validation = $this->stockValidation->validate($item->product, $dto->quantity);

        if (! $validation->allowed) {
            return $validation;
        }

        $item->update(['quantity' => $validation->allowedQuantity]);

        return $validation;
    }
}
