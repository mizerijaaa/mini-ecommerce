<?php

namespace Database\Factories\Domain\Cart\Models;

use App\Domain\Cart\Models\Cart;
use App\Domain\Cart\Models\CartItem;
use App\Domain\ProductCatalog\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CartItem>
 */
class CartItemFactory extends Factory
{
    /** @var class-string<\Illuminate\Database\Eloquent\Model> */
    protected $model = CartItem::class;

    public function definition(): array
    {
        return [
            'cart_id' => Cart::factory(),
            'product_id' => Product::factory(),
            'quantity' => fake()->numberBetween(1, 5),
        ];
    }
}

