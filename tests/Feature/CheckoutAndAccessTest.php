<?php

use App\Domain\Cart\Actions\AddToCartAction;
use App\Domain\Cart\DTOs\AddToCartDTO;
use App\Domain\Cart\Models\Cart;
use App\Domain\Cart\Models\CartItem;
use App\Domain\OrderManagement\Actions\UpdateOrderItemStatusAction;
use App\Domain\OrderManagement\DTOs\CheckoutDTO;
use App\Domain\OrderManagement\Enums\OrderStatus;
use App\Domain\OrderManagement\Exceptions\CheckoutException;
use App\Domain\OrderManagement\Exceptions\PaymentFailedException;
use App\Domain\OrderManagement\Models\Order;
use App\Domain\OrderManagement\Models\OrderItem;
use App\Domain\OrderManagement\Services\CheckoutService;
use App\Domain\ProductCatalog\Models\Product;
use App\Domain\ProductCatalog\Models\Vendor;
use App\Models\User;

it('successful checkout decreases stock and clears cart', function () {
    $buyer = User::factory()->create(['role' => 'buyer']);

    $vendor = Vendor::factory()->create();

    $product = Product::factory()->create([
        'vendor_id' => $vendor->id,
        'status' => 'active',
        'price' => 20.00,
        'stock' => 10,
    ]);

    $cart = Cart::query()->create(['user_id' => (string) $buyer->id]);
    CartItem::query()->create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'quantity' => 2,
    ]);

    $order = app(CheckoutService::class)->checkout(new CheckoutDTO(
        userId: (string) $buyer->id,
        paymentMethod: 'card',
    ));

    expect($order)->toBeInstanceOf(Order::class);
    expect($order->status)->toBe(OrderStatus::Paid);
    expect($order->items()->count())->toBe(1);

    $product->refresh();
    expect($product->stock)->toBe(8);

    expect(CartItem::query()->where('cart_id', $cart->id)->count())->toBe(0);

    $orderItem = $order->items()->first();
    expect($orderItem->status)->toBe(OrderStatus::Paid);
});

it('failed checkout due to payment total > 999 keeps cart intact', function () {
    $buyer = User::factory()->create(['role' => 'buyer']);
    $vendor = Vendor::factory()->create();

    $product = Product::factory()->create([
        'vendor_id' => $vendor->id,
        'status' => 'active',
        'price' => 1000.00,
        'stock' => 10,
    ]);

    $cart = Cart::query()->create(['user_id' => (string) $buyer->id]);
    $item = CartItem::query()->create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'quantity' => 1,
    ]);

    expect(fn () => app(CheckoutService::class)->checkout(new CheckoutDTO(
        userId: (string) $buyer->id,
        paymentMethod: 'card',
    )))->toThrow(PaymentFailedException::class);

    expect(CartItem::query()->whereKey($item->id)->exists())->toBeTrue();

    $product->refresh();
    expect($product->stock)->toBe(10);
});

it('cart stock validation prevents exceeding stock (clamps with warning)', function () {
    $buyer = User::factory()->create(['role' => 'buyer']);
    $vendor = Vendor::factory()->create();

    $product = Product::factory()->create([
        'vendor_id' => $vendor->id,
        'status' => 'active',
        'stock' => 3,
        'price' => 10.00,
    ]);

    $result = app(AddToCartAction::class)->execute(new AddToCartDTO(
        userId: (string) $buyer->id,
        productId: (string) $product->id,
        quantity: 5,
    ));

    expect($result->allowed)->toBeTrue();
    expect($result->allowedQuantity)->toBe(3);
    expect($result->warning)->not()->toBeNull();

    $cart = Cart::query()->where('user_id', (string) $buyer->id)->firstOrFail();
    $item = CartItem::query()->where('cart_id', $cart->id)->where('product_id', $product->id)->firstOrFail();

    expect($item->quantity)->toBe(3);
});

it('buyer cannot access vendor routes', function () {
    $buyer = User::factory()->create(['role' => 'buyer']);

    $this->actingAs($buyer)
        ->get('/vendor/orders')
        ->assertForbidden();
});

it('invalid order status transitions are blocked', function () {
    $vendor = Vendor::factory()->create();
    $vendorUser = User::query()->findOrFail($vendor->user_id);

    $buyer = User::factory()->create(['role' => 'buyer']);
    $order = Order::query()->create([
        'user_id' => (string) $buyer->id,
        'payment_method' => 'card',
        'status' => OrderStatus::Paid,
    ]);

    $product = Product::factory()->create([
        'vendor_id' => $vendor->id,
        'status' => 'active',
        'price' => 25.00,
        'stock' => 10,
    ]);

    $item = OrderItem::query()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'vendor_id' => $vendor->id,
        'quantity' => 1,
        'price' => 25.00,
        'status' => OrderStatus::Shipped,
    ]);

    expect(fn () => app(UpdateOrderItemStatusAction::class)->execute(
        vendorId: (string) $vendor->id,
        orderItemId: (string) $item->id,
        to: OrderStatus::Paid,
    ))->toThrow(CheckoutException::class);
});
