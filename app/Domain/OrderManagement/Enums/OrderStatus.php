<?php

namespace App\Domain\OrderManagement\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Shipped = 'shipped';
    case Delivered = 'delivered';

    public function canTransitionTo(self $to): bool
    {
        $order = [
            self::Pending->value => 0,
            self::Paid->value => 1,
            self::Shipped->value => 2,
            self::Delivered->value => 3,
        ];

        return $order[$to->value] >= $order[$this->value];
    }
}
