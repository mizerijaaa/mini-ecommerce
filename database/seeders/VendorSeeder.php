<?php

namespace Database\Seeders;

use App\Domain\ProductCatalog\Models\Vendor;
use Illuminate\Database\Seeder;

class VendorSeeder extends Seeder
{
    public function run(): void
    {
        Vendor::factory()
            ->count(fake()->numberBetween(3, 5))
            ->create();
    }
}
