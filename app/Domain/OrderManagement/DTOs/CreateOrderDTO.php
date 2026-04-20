<?php

namespace App\Domain\OrderManagement\DTOs;

readonly class CreateOrderDTO
{
    public function __construct(
        public string $userId,
        public string $paymentMethod,
        public string $status = 'pending',
    ) {}
}
