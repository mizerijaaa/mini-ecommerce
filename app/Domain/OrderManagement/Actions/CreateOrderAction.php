<?php

namespace App\Domain\OrderManagement\Actions;

use App\Domain\OrderManagement\DTOs\CreateOrderDTO;
use App\Domain\OrderManagement\Models\Order;

class CreateOrderAction
{
    public function execute(CreateOrderDTO $dto): Order
    {
        return Order::query()->create([
            'user_id' => $dto->userId,
            'payment_method' => $dto->paymentMethod,
            'status' => $dto->status,
        ]);
    }
}
