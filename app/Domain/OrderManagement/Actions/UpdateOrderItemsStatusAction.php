<?php

namespace App\Domain\OrderManagement\Actions;

use App\Domain\OrderManagement\Models\Order;

class UpdateOrderItemsStatusAction
{
    public function execute(Order $order, string $status): void
    {
        $order->items()->update(['status' => $status]);
    }
}
