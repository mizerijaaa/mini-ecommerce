<?php

namespace Database\Seeders;

use App\Domain\ProductCatalog\Models\Product;
use App\Domain\ProductCatalog\Models\Vendor;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        Vendor::query()
            ->withCount('products')
            ->get()
            ->each(function (Vendor $vendor): void {
                $targetCount = fake()->numberBetween(10, 16);
                $createCount = max(0, $targetCount - (int) $vendor->products_count);

                if ($createCount === 0) {
                    return;
                }

                Product::factory()
                    ->count($createCount)
                    ->state([
                        'vendor_id' => $vendor->id,
                        'status' => 'active',
                    ])
                    ->create();
            });
    }
}
