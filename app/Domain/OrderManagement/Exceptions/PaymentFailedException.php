<?php

namespace App\Domain\OrderManagement\Exceptions;

use RuntimeException;

class PaymentFailedException extends RuntimeException
{
    public function __construct(
        public readonly ?string $orderId = null,
        string $message = 'Payment was rejected.',
    ) {
        parent::__construct($message);
    }
}
