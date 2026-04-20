<?php

use App\Domain\Cart\Actions\AddToCartAction;
use App\Domain\Cart\DTOs\AddToCartDTO;
use App\Domain\Cart\Models\CartItem;
use App\Domain\OrderManagement\DTOs\CheckoutDTO;
use App\Domain\OrderManagement\Exceptions\CheckoutException;
use App\Domain\OrderManagement\Services\CheckoutService;
use App\Domain\ProductCatalog\Enums\ProductStatus;
use App\Domain\ProductCatalog\Models\Product;
use App\Domain\ProductCatalog\Models\Vendor;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

it('draft and archived products are not addable to cart', function () {
    $buyer = User::factory()->create(['role' => 'buyer']);
    $vendor = Vendor::factory()->create();

    $draft = Product::factory()->create(['vendor_id' => $vendor->id, 'status' => ProductStatus::Draft, 'stock' => 10]);
    $archived = Product::factory()->create(['vendor_id' => $vendor->id, 'status' => ProductStatus::Archived, 'stock' => 10]);

    expect(fn () => app(AddToCartAction::class)->execute(new AddToCartDTO(
        userId: (string) $buyer->id,
        productId: (string) $draft->id,
        quantity: 1,
    )))->toThrow(ModelNotFoundException::class);

    expect(fn () => app(AddToCartAction::class)->execute(new AddToCartDTO(
        userId: (string) $buyer->id,
        productId: (string) $archived->id,
        quantity: 1,
    )))->toThrow(ModelNotFoundException::class);

    expect(CartItem::query()->count())->toBe(0);
});

it('checkout rejects cart items that are no longer active (e.g. archived while in cart)', function () {
    $buyer = User::factory()->create(['role' => 'buyer']);
    $vendor = Vendor::factory()->create();

    $product = Product::factory()->create(['vendor_id' => $vendor->id, 'status' => ProductStatus::Active, 'stock' => 10, 'price' => 10.00]);

    app(AddToCartAction::class)->execute(new AddToCartDTO(
        userId: (string) $buyer->id,
        productId: (string) $product->id,
        quantity: 1,
    ));

    $product->update(['status' => ProductStatus::Archived]);

    expect(fn () => app(CheckoutService::class)->checkout(new CheckoutDTO(
        userId: (string) $buyer->id,
        paymentMethod: 'card',
    )))->toThrow(CheckoutException::class);
});
