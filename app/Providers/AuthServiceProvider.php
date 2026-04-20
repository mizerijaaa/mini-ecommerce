<?php

namespace App\Providers;

use App\Domain\OrderManagement\Models\Order;
use App\Domain\OrderManagement\Policies\OrderPolicy;
use App\Domain\ProductCatalog\Models\Product;
use App\Domain\ProductCatalog\Policies\ProductPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Product::class => ProductPolicy::class,
        Order::class => OrderPolicy::class,
    ];
}
