<?php

namespace Database\Seeders;

use App\Domain\IdentityAndAccess\Models\User;
use Illuminate\Database\Seeder;

class BuyerSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()
            ->count(12)
            ->state(['role' => 'buyer'])
            ->create();
    }
}

