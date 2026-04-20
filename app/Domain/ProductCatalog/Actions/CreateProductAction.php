<?php

namespace App\Domain\ProductCatalog\Actions;

use App\Domain\IdentityAndAccess\Models\User;
use App\Domain\ProductCatalog\DTOs\CreateProductDTO;
use App\Domain\ProductCatalog\Enums\ProductStatus;
use App\Domain\ProductCatalog\Models\Product;
use Illuminate\Support\Facades\Gate;

class CreateProductAction
{
    public function execute(CreateProductDTO $dto): Product
    {
        $user = User::query()->findOrFail($dto->userId);

        Gate::forUser($user)->authorize('create', Product::class);

        $vendorId = (string) ($user->vendor?->id ?? '');

        if ($vendorId === '') {
            abort(403);
        }

        if (! in_array($dto->status, ProductStatus::values(), true)) {
            abort(422);
        }

        return Product::query()->create([
            'vendor_id' => $vendorId,
            'name' => trim($dto->name),
            'description' => trim($dto->description),
            'price' => max(0, (float) $dto->price),
            'stock' => max(0, (int) $dto->stock),
            'image_url' => $dto->imageUrl ? trim($dto->imageUrl) : null,
            'status' => $dto->status,
        ]);
    }
}
