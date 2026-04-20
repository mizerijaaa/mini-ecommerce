<?php

namespace Database\Factories\Domain\OrderManagement\Models;

use App\Domain\OrderManagement\Models\Order;
use App\Domain\OrderManagement\Models\OrderItem;
use App\Domain\ProductCatalog\Models\Product;
use App\Domain\ProductCatalog\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    /** @var class-string<Model> */
    protected $model = OrderItem::class;

    public function definition(): array
    {
        $price = fake()->randomFloat(2, 4.99, 249.99);

        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'vendor_id' => Vendor::factory(),
            'quantity' => fake()->numberBetween(1, 5),
            'price' => $price,
        ];
    }
}
