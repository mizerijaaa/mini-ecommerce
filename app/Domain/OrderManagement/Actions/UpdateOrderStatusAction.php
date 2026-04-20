<?php

namespace App\Domain\OrderManagement\Actions;

use App\Domain\OrderManagement\Models\Order;

class UpdateOrderStatusAction
{
    public function execute(Order $order, string $status): Order
    {
        $order->update(['status' => $status]);

        return $order;
    }
}
