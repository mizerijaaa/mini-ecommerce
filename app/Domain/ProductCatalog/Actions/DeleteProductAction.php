<?php

namespace App\Domain\ProductCatalog\Actions;

use App\Domain\IdentityAndAccess\Models\User;
use App\Domain\ProductCatalog\DTOs\DeleteProductDTO;
use App\Domain\ProductCatalog\Models\Product;
use Illuminate\Support\Facades\Gate;

class DeleteProductAction
{
    public function execute(DeleteProductDTO $dto): void
    {
        $user = User::query()->findOrFail($dto->userId);
        $product = Product::query()->findOrFail($dto->productId);

        Gate::forUser($user)->authorize('delete', $product);

        $product->delete();
    }
}
