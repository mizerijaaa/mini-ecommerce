<?php

namespace App\Domain\OrderManagement\Actions;

use App\Domain\OrderManagement\Enums\OrderStatus;
use App\Domain\OrderManagement\Exceptions\CheckoutException;
use App\Domain\OrderManagement\Models\OrderItem;

class UpdateOrderItemStatusAction
{
    public function execute(string $vendorId, string $orderItemId, OrderStatus $to): OrderItem
    {
        $item = OrderItem::query()
            ->with('product')
            ->whereKey($orderItemId)
            ->where('vendor_id', $vendorId)
            ->firstOrFail();

        $from = $item->status ?? OrderStatus::Pending;

        if (! $from->canTransitionTo($to)) {
            throw new CheckoutException('Invalid status transition.');
        }

        $item->update(['status' => $to]);

        return $item;
    }
}
