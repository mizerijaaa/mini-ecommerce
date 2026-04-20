<?php

namespace Database\Seeders;

use App\Domain\IdentityAndAccess\Models\User;
use App\Domain\ProductCatalog\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DevAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password');

        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'role' => 'admin',
                'password' => $password,
            ],
        );

        $vendorUser = User::query()->updateOrCreate(
            ['email' => 'vendor@example.com'],
            [
                'name' => 'Vendor',
                'role' => 'vendor',
                'password' => $password,
            ],
        );

        Vendor::query()->updateOrCreate(
            ['user_id' => $vendorUser->id],
            ['name' => 'Demo Vendor Store'],
        );

        User::query()->updateOrCreate(
            ['email' => 'buyer@example.com'],
            [
                'name' => 'Buyer',
                'role' => 'buyer',
                'password' => $password,
            ],
        );

        // Keep the existing "test@example.com" user for convenience, but ensure it can log in.
        User::query()->updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test Buyer',
                'role' => 'buyer',
                'password' => $password,
            ],
        );
    }
}
