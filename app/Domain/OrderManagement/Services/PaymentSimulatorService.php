<?php

namespace App\Domain\OrderManagement\Services;

use App\Domain\OrderManagement\DTOs\PaymentResultDTO;

class PaymentSimulatorService
{
    public function charge(float $total): PaymentResultDTO
    {
        if ($total > 999) {
            return PaymentResultDTO::failure('Payment was rejected (order total over $999).');
        }

        return PaymentResultDTO::success();
    }
}
