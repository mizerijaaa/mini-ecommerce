<?php

use App\Domain\OrderManagement\Actions\UpdateOrderItemStatusAction;
use App\Domain\OrderManagement\Enums\OrderStatus;
use App\Domain\OrderManagement\Models\Order;
use App\Domain\OrderManagement\Models\OrderItem;
use App\Domain\ProductCatalog\Actions\CreateProductAction;
use App\Domain\ProductCatalog\Actions\DeleteProductAction;
use App\Domain\ProductCatalog\DTOs\CreateProductDTO;
use App\Domain\ProductCatalog\DTOs\DeleteProductDTO;
use App\Domain\ProductCatalog\Models\Product;
use App\Domain\ProductCatalog\Models\Vendor;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

it('vendor can create a product (draft/active/archived)', function () {
    $vendor = Vendor::factory()->create();
    $vendorUser = User::query()->findOrFail($vendor->user_id);

    $product = app(CreateProductAction::class)->execute(new CreateProductDTO(
        userId: (string) $vendorUser->id,
        name: 'Vendor Product',
        description: 'A vendor created product.',
        price: 19.99,
        stock: 10,
        imageUrl: null,
        status: 'draft',
    ));

    expect($product)->toBeInstanceOf(Product::class);
    expect($product->vendor_id)->toBe($vendor->id);
    expect($product->status)->toBe('draft');
});

it('vendor can delete their own product but not others', function () {
    $vendorA = Vendor::factory()->create();
    $vendorAUser = User::query()->findOrFail($vendorA->user_id);

    $vendorB = Vendor::factory()->create();
    $vendorBUser = User::query()->findOrFail($vendorB->user_id);

    $own = Product::factory()->create(['vendor_id' => $vendorA->id]);
    $other = Product::factory()->create(['vendor_id' => $vendorB->id]);

    app(DeleteProductAction::class)->execute(new DeleteProductDTO(
        userId: (string) $vendorAUser->id,
        productId: (string) $own->id,
    ));

    expect(Product::query()->whereKey($own->id)->exists())->toBeFalse();

    expect(fn () => app(DeleteProductAction::class)->execute(new DeleteProductDTO(
        userId: (string) $vendorAUser->id,
        productId: (string) $other->id,
    )))->toThrow(AuthorizationException::class);
});

it('vendor orders page shows only order items belonging to that vendor', function () {
    $vendorA = Vendor::factory()->create();
    $vendorAUser = User::query()->findOrFail($vendorA->user_id);

    $vendorB = Vendor::factory()->create();

    $buyer = User::factory()->create(['role' => 'buyer']);
    $order = Order::query()->create([
        'user_id' => (string) $buyer->id,
        'payment_method' => 'card',
        'status' => OrderStatus::Paid,
    ]);

    $productA = Product::factory()->create(['vendor_id' => $vendorA->id, 'name' => 'Product A']);
    $productB = Product::factory()->create(['vendor_id' => $vendorB->id, 'name' => 'Product B']);

    OrderItem::query()->create([
        'order_id' => $order->id,
        'product_id' => $productA->id,
        'vendor_id' => $vendorA->id,
        'quantity' => 1,
        'price' => 10.00,
        'status' => OrderStatus::Paid,
    ]);

    OrderItem::query()->create([
        'order_id' => $order->id,
        'product_id' => $productB->id,
        'vendor_id' => $vendorB->id,
        'quantity' => 1,
        'price' => 10.00,
        'status' => OrderStatus::Paid,
    ]);

    $this->actingAs($vendorAUser)
        ->get('/vendor/orders')
        ->assertOk()
        ->assertSee('Product A')
        ->assertDontSee('Product B');
});

it('vendor can update status of their order items forward only (e.g. paid -> shipped)', function () {
    $vendor = Vendor::factory()->create();
    $vendorUser = User::query()->findOrFail($vendor->user_id);

    $buyer = User::factory()->create(['role' => 'buyer']);
    $order = Order::query()->create([
        'user_id' => (string) $buyer->id,
        'payment_method' => 'card',
        'status' => OrderStatus::Paid,
    ]);

    $product = Product::factory()->create(['vendor_id' => $vendor->id]);
    $item = OrderItem::query()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'vendor_id' => $vendor->id,
        'quantity' => 1,
        'price' => 25.00,
        'status' => OrderStatus::Paid,
    ]);

    $this->actingAs($vendorUser)
        ->get('/vendor/orders')
        ->assertOk();

    $updated = app(UpdateOrderItemStatusAction::class)->execute(
        vendorId: (string) $vendor->id,
        orderItemId: (string) $item->id,
        to: OrderStatus::Shipped,
    );

    expect($updated->status)->toBe(OrderStatus::Shipped);
});
