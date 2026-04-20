<?php

namespace App\Domain\OrderManagement\Actions;

use App\Domain\Cart\Models\Cart;

class ClearCartAction
{
    public function execute(Cart $cart): void
    {
        $cart->items()->delete();
    }
}
