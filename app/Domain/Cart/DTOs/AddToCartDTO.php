<?php

namespace App\Domain\Cart\DTOs;

readonly class AddToCartDTO
{
    public function __construct(
        public string $userId,
        public string $productId,
        public int $quantity,
    ) {}
}
