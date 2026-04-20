<?php

namespace App\Domain\OrderManagement\DTOs;

readonly class PaymentResultDTO
{
    public function __construct(
        public bool $success,
        public string $message,
    ) {}

    public static function success(string $message = 'Payment successful.'): self
    {
        return new self(true, $message);
    }

    public static function failure(string $message): self
    {
        return new self(false, $message);
    }
}
