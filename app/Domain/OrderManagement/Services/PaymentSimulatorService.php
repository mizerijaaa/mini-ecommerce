<?php

namespace App\Domain\OrderManagement\Services;

use App\Domain\OrderManagement\Exceptions\PaymentFailedException;

class PaymentSimulatorService
{
    public function charge(float $total, ?string $orderId = null): void
    {
        if ($total > 999) {
            throw new PaymentFailedException($orderId, 'Payment was rejected (order total over $999).');
        }
    }
}
