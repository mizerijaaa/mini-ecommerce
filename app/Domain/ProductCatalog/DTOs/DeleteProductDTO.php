<?php

namespace App\Domain\ProductCatalog\DTOs;

class DeleteProductDTO
{
    public function __construct(
        public string $userId,
        public string $productId,
    ) {}
}
