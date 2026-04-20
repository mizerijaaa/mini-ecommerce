<?php

namespace Database\Factories\Domain\OrderManagement\Models;

use App\Domain\IdentityAndAccess\Models\User;
use App\Domain\OrderManagement\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /** @var class-string<\Illuminate\Database\Eloquent\Model> */
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->state(['role' => 'buyer']),
            'status' => fake()->randomElement(['pending', 'paid', 'shipped', 'delivered']),
            'payment_method' => fake()->randomElement(['card', 'cash_on_delivery']),
        ];
    }
}

