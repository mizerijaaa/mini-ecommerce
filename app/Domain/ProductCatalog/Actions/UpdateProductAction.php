<?php

namespace App\Domain\ProductCatalog\Actions;

use App\Domain\IdentityAndAccess\Models\User;
use App\Domain\ProductCatalog\DTOs\UpdateProductDTO;
use App\Domain\ProductCatalog\Models\Product;
use Illuminate\Support\Facades\Gate;

class UpdateProductAction
{
    public function execute(UpdateProductDTO $dto): Product
    {
        $user = User::query()->findOrFail($dto->userId);
        $product = Product::query()->findOrFail($dto->productId);

        Gate::forUser($user)->authorize('update', $product);

        $updates = [];

        if ($dto->status !== null) {
            $updates['status'] = $dto->status;
        }

        if ($dto->stock !== null) {
            $updates['stock'] = max(0, (int) $dto->stock);
        }

        if ($updates !== []) {
            $product->fill($updates);
            $product->save();
        }

        return $product->refresh();
    }
}
