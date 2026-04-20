<?php

namespace App\Domain\OrderManagement\DTOs;

readonly class CheckoutDTO
{
    public function __construct(
        public string $userId,
        public string $paymentMethod,
    ) {}
}
