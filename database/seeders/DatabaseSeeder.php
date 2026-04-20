<?php

namespace Database\Seeders;

use App\Domain\IdentityAndAccess\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test Buyer',
            'email' => 'test@example.com',
            'role' => 'buyer',
        ]);

        $this->call([
            VendorSeeder::class,
            ProductSeeder::class,
            BuyerSeeder::class,
            CartSeeder::class,
        ]);
    }
}
