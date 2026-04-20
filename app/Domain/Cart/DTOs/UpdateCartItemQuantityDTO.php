<?php

namespace App\Domain\Cart\DTOs;

readonly class UpdateCartItemQuantityDTO
{
    public function __construct(
        public string $userId,
        public string $cartItemId,
        public int $quantity,
    ) {}
}
