<?php

namespace App\Domain\Cart\Actions;

use App\Domain\Cart\DTOs\RemoveFromCartDTO;
use App\Domain\Cart\Models\CartItem;

class RemoveFromCartAction
{
    public function execute(RemoveFromCartDTO $dto): void
    {
        CartItem::query()
            ->whereKey($dto->cartItemId)
            ->whereHas('cart', fn ($q) => $q->where('user_id', $dto->userId))
            ->delete();
    }
}
