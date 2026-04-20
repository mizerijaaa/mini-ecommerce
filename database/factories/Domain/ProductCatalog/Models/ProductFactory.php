<?php

namespace Database\Factories\Domain\ProductCatalog\Models;

use App\Domain\ProductCatalog\Models\Product;
use App\Domain\ProductCatalog\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /** @var class-string<Model> */
    protected $model = Product::class;

    public function definition(): array
    {
        $adjectives = ['Premium', 'Organic', 'Handmade', 'Classic', 'Modern', 'Compact', 'Durable', 'Eco-Friendly', 'Limited Edition', 'Essential'];
        $productTypes = [
            'Canvas Backpack',
            'Wireless Earbuds',
            'Ceramic Mug',
            'Stainless Water Bottle',
            'Yoga Mat',
            'Scented Candle',
            'Desk Lamp',
            'Bluetooth Speaker',
            'Phone Case',
            'Notebook Set',
            'Kitchen Knife',
            'Skincare Set',
            'Travel Organizer',
            'Socks Pack',
            'T-Shirt',
            'Coffee Beans',
            'Tea Sampler',
            'Gaming Mousepad',
            'Fitness Resistance Bands',
            'Portable Charger',
        ];

        $name = fake()->randomElement($adjectives).' '.fake()->randomElement($productTypes);
        $price = fake()->randomFloat(2, 4.99, 249.99);
        $imageText = rawurlencode(mb_substr($name, 0, 28));

        return [
            'vendor_id' => Vendor::factory(),
            'name' => $name,
            'description' => fake()->paragraphs(fake()->numberBetween(1, 3), true),
            'price' => $price,
            'stock' => fake()->numberBetween(0, 250),
            'image_url' => fake()->boolean(70)
                ? "https://placehold.co/900x900/png?text={$imageText}"
                : null,
            'status' => fake()->randomElement(['draft', 'active', 'archived']),
        ];
    }
}
