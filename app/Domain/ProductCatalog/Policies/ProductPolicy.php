<?php

namespace App\Domain\ProductCatalog\Policies;

use App\Domain\IdentityAndAccess\Models\User;
use App\Domain\ProductCatalog\Models\Product;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isVendor();
    }

    public function view(User $user, Product $product): bool
    {
        return $user->isAdmin() || ($user->isVendor() && $user->vendor?->id === $product->vendor_id);
    }

    public function create(User $user): bool
    {
        return $user->isVendor();
    }

    public function update(User $user, Product $product): bool
    {
        return $user->isAdmin() || ($user->isVendor() && $user->vendor?->id === $product->vendor_id);
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->isAdmin() || ($user->isVendor() && $user->vendor?->id === $product->vendor_id);
    }
}
