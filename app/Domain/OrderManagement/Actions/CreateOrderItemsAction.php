<?php

namespace App\Domain\OrderManagement\Actions;

use App\Domain\Cart\Models\CartItem;
use App\Domain\OrderManagement\Enums\OrderStatus;
use App\Domain\OrderManagement\Models\Order;
use App\Domain\OrderManagement\Models\OrderItem;
use Illuminate\Support\Collection;

class CreateOrderItemsAction
{
    /**
     * @param  Collection<int, CartItem>  $cartItems
     */
    public function execute(Order $order, Collection $cartItems): void
    {
        foreach ($cartItems as $cartItem) {
            $product = $cartItem->product;

            OrderItem::query()->create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'vendor_id' => $product->vendor_id,
                'quantity' => $cartItem->quantity,
                'price' => $product->price,
                'status' => OrderStatus::Pending,
            ]);
        }
    }
}
