<?php

namespace App\Domain\Cart\DTOs;

readonly class StockValidationResultDTO
{
    public function __construct(
        public bool $allowed,
        public int $allowedQuantity,
        public ?string $warning,
    ) {}

    public static function allowed(int $quantity): self
    {
        return new self(true, $quantity, null);
    }

    public static function warned(int $quantity, string $warning): self
    {
        return new self(true, $quantity, $warning);
    }

    public static function denied(string $warning): self
    {
        return new self(false, 0, $warning);
    }
}
