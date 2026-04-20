<?php

namespace App\Domain\Cart\DTOs;

readonly class RemoveFromCartDTO
{
    public function __construct(
        public string $userId,
        public string $cartItemId,
    ) {}
}
