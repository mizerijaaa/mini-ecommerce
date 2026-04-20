<?php

namespace Database\Seeders;

use App\Domain\Cart\Models\Cart;
use App\Domain\Cart\Models\CartItem;
use App\Domain\IdentityAndAccess\Models\User;
use App\Domain\ProductCatalog\Enums\ProductStatus;
use App\Domain\ProductCatalog\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class CartSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::query()
            ->where('status', ProductStatus::Active)
            ->get(['id', 'stock']);

        if ($products->isEmpty()) {
            return;
        }

        User::query()
            ->where('role', 'buyer')
            ->get(['id'])
            ->each(function (User $buyer) use ($products): void {
                $cart = Cart::query()->firstOrCreate(['user_id' => $buyer->id]);

                $itemsCount = fake()->numberBetween(2, 6);

                /** @var Collection<int, Product> $picked */
                $picked = $products->random(min($itemsCount, $products->count()));

                foreach ($picked as $product) {
                    $maxQty = max(1, min(5, (int) $product->stock));

                    CartItem::query()->updateOrCreate(
                        ['cart_id' => $cart->id, 'product_id' => $product->id],
                        ['quantity' => fake()->numberBetween(1, $maxQty)]
                    );
                }
            });
    }
}
