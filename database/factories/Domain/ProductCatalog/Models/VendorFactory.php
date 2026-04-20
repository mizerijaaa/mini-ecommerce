<?php

namespace Database\Factories\Domain\ProductCatalog\Models;

use App\Domain\IdentityAndAccess\Models\User;
use App\Domain\ProductCatalog\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vendor>
 */
class VendorFactory extends Factory
{
    /** @var class-string<\Illuminate\Database\Eloquent\Model> */
    protected $model = Vendor::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->state(['role' => 'vendor']),
            'name' => fake()->company(),
        ];
    }
}

