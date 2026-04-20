<?php

namespace App\Domain\OrderManagement\Policies;

use App\Domain\IdentityAndAccess\Models\User;
use App\Domain\OrderManagement\Models\Order;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Order $order): bool
    {
        return $user->isAdmin() || $order->user_id === $user->id;
    }
}
