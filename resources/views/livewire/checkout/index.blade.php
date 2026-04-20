<?php

use App\Domain\Cart\Models\Cart;
use App\Domain\OrderManagement\DTOs\CheckoutDTO;
use App\Domain\OrderManagement\Exceptions\CheckoutException;
use App\Domain\OrderManagement\Exceptions\PaymentFailedException;
use App\Domain\OrderManagement\Services\CheckoutService;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public string $paymentMethod = 'card';
    public ?string $error = null;

    public function getCartProperty(): Cart
    {
        $userId = (string) Auth::id();

        return Cart::query()
            ->firstOrCreate(['user_id' => $userId])
            ->load(['items.product.vendor']);
    }

    public function getTotalProperty(): float
    {
        return (float) $this->cart->items->sum(fn ($i) => (float) $i->product->price * (int) $i->quantity);
    }

    public function placeOrder(): void
    {
        $this->error = null;

        try {
            $order = app(CheckoutService::class)->checkout(new CheckoutDTO(
                userId: (string) Auth::id(),
                paymentMethod: $this->paymentMethod,
            ));
        } catch (PaymentFailedException|CheckoutException $e) {
            $this->error = $e->getMessage();

            return;
        }

        $this->redirectRoute('buyer.orders.show', ['orderId' => $order->id], navigate: true);
    }
}->layout('layouts.app');

?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-start justify-between gap-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Checkout</h1>
            <p class="mt-1 text-sm text-gray-600">Review your items and place your order.</p>
        </div>

        <a href="{{ route('cart.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
            Back to cart
        </a>
    </div>

    @if ($error)
        <div class="mt-6 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-900">
            {{ $error }}
        </div>
    @endif

    <div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-4">
            <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-200 overflow-hidden">
                <div class="px-4 py-3 bg-gray-50">
                    <div class="text-sm font-semibold text-gray-900">Items</div>
                </div>

                @if ($this->cart->items->count() === 0)
                    <div class="p-8 text-center">
                        <div class="text-base font-medium text-gray-900">Your cart is empty</div>
                        <div class="mt-1 text-sm text-gray-600">Add items before checking out.</div>
                        <div class="mt-4">
                            <a href="{{ route('market.index') }}" class="inline-flex items-center rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">
                                Browse marketplace
                            </a>
                        </div>
                    </div>
                @else
                    <div class="divide-y divide-gray-100">
                        @foreach ($this->cart->items as $item)
                            <div class="p-4 flex items-start justify-between gap-6">
                                <div class="min-w-0">
                                    <div class="text-sm font-semibold text-gray-900 truncate">
                                        {{ $item->product?->name }}
                                    </div>
                                    <div class="mt-1 text-xs text-gray-500">
                                        {{ $item->product?->vendor?->name }} · Qty {{ $item->quantity }}
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-semibold text-gray-900">
                                        ${{ number_format((float) $item->product?->price, 2) }}
                                    </div>
                                    <div class="mt-1 text-xs text-gray-500">
                                        Line: ${{ number_format((float) $item->product?->price * (int) $item->quantity, 2) }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-200 p-4">
                <div class="text-sm font-semibold text-gray-900">Payment method</div>
                <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <label class="flex items-center gap-3 rounded-lg border border-gray-200 p-3 hover:bg-gray-50 cursor-pointer">
                        <input type="radio" class="text-indigo-600 focus:ring-indigo-500" wire:model.live="paymentMethod" value="card" />
                        <div class="min-w-0">
                            <div class="text-sm font-medium text-gray-900">Card</div>
                            <div class="text-xs text-gray-500">Simulated payment</div>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 rounded-lg border border-gray-200 p-3 hover:bg-gray-50 cursor-pointer">
                        <input type="radio" class="text-indigo-600 focus:ring-indigo-500" wire:model.live="paymentMethod" value="cash_on_delivery" />
                        <div class="min-w-0">
                            <div class="text-sm font-medium text-gray-900">Cash on delivery</div>
                            <div class="text-xs text-gray-500">Pay when it arrives</div>
                        </div>
                    </label>
                </div>
                <div class="mt-3 text-xs text-gray-500">
                    Note: orders over $999 will be rejected by the payment simulator.
                </div>
            </div>
        </div>

        <div class="h-fit rounded-xl bg-white shadow-sm ring-1 ring-gray-200 p-4 lg:sticky lg:top-6">
            <div class="text-sm font-semibold text-gray-900">Order summary</div>

            <div class="mt-3 space-y-2 text-sm">
                <div class="flex items-center justify-between">
                    <div class="text-gray-600">Items</div>
                    <div class="font-medium text-gray-900">{{ $this->cart->items->count() }}</div>
                </div>
                <div class="flex items-center justify-between">
                    <div class="text-gray-600">Total</div>
                    <div class="font-semibold text-gray-900">${{ number_format((float) $this->total, 2) }}</div>
                </div>
            </div>

            <button
                type="button"
                wire:click="placeOrder"
                wire:loading.attr="disabled"
                class="mt-4 inline-flex w-full items-center justify-center rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800 disabled:opacity-50 disabled:cursor-not-allowed"
            >
                <span wire:loading.remove>Place order</span>
                <span wire:loading>Placing order…</span>
            </button>
        </div>
    </div>
</div>

