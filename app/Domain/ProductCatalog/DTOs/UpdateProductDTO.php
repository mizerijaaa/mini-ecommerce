<?php

namespace App\Domain\ProductCatalog\DTOs;

class UpdateProductDTO
{
    public function __construct(
        public string $userId,
        public string $productId,
        public ?string $status = null,
        public ?int $stock = null,
    ) {}
}
