<?php

namespace App\Domain\ProductCatalog\DTOs;

class CreateProductDTO
{
    public function __construct(
        public string $userId,
        public string $name,
        public string $description,
        public float $price,
        public int $stock,
        public ?string $imageUrl,
        public string $status,
    ) {}
}
