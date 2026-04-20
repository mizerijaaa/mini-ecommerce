<?php

namespace Database\Factories\Domain\Cart\Models;

use App\Domain\Cart\Models\Cart;
use App\Domain\IdentityAndAccess\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends Factory<Cart>
 */
class CartFactory extends Factory
{
    /** @var class-string<Model> */
    protected $model = Cart::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->state(['role' => 'buyer']),
        ];
    }
}
