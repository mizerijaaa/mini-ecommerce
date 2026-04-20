<?php

use App\Domain\Cart\Actions\RemoveFromCartAction;
use App\Domain\Cart\Actions\UpdateCartItemQuantityAction;
use App\Domain\Cart\DTOs\RemoveFromCartDTO;
use App\Domain\Cart\DTOs\UpdateCartItemQuantityDTO;
use App\Domain\Cart\Models\Cart;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use function Livewire\Volt\layout;

new class extends Component {
    public ?string $warning = null;
    public ?string $notice = null;

    public function remove(string $cartItemId): void
    {
        $userId = (string) Auth::id();

        app(RemoveFromCartAction::class)->execute(new RemoveFromCartDTO(
            userId: $userId,
            cartItemId: $cartItemId,
        ));

        $this->warning = null;
        $this->notice = 'Item removed.';
    }

    public function updateQuantity(string $cartItemId, $quantity): void
    {
        $userId = (string) Auth::id();
        $qty = max(1, (int) $quantity);

        $result = app(UpdateCartItemQuantityAction::class)->execute(new UpdateCartItemQuantityDTO(
            userId: $userId,
            cartItemId: $cartItemId,
            quantity: $qty,
        ));

        $this->warning = $result->warning;
        $this->notice = $result->warning ? null : 'Quantity updated.';
    }

    public function getCartProperty(): Cart
    {
        $userId = (string) Auth::id();

        return Cart::query()
            ->firstOrCreate(['user_id' => $userId])
            ->load(['items.product.vendor']);
    }

    public function getGroupedItemsProperty(): array
    {
        $groups = [];

        foreach ($this->cart->items as $item) {
            $vendorId = (string) ($item->product?->vendor_id ?? 'unknown');
            $vendorName = $item->product?->vendor?->name ?? 'Unknown vendor';

            if (! array_key_exists($vendorId, $groups)) {
                $groups[$vendorId] = [
                    'vendor_name' => $vendorName,
                    'items' => [],
                    'subtotal' => 0.0,
                ];
            }

            $lineTotal = (float) $item->product->price * (int) $item->quantity;
            $groups[$vendorId]['items'][] = [
                'id' => $item->id,
                'product_name' => $item->product->name,
                'image_url' => $item->product->image_url,
                'price' => (float) $item->product->price,
                'stock' => (int) $item->product->stock,
                'quantity' => (int) $item->quantity,
                'line_total' => $lineTotal,
            ];
            $groups[$vendorId]['subtotal'] += $lineTotal;
        }

        return $groups;
    }

    public function getGrandTotalProperty(): float
    {
        return array_reduce($this->groupedItems, fn (float $carry, array $g) => $carry + (float) $g['subtotal'], 0.0);
    }
};

layout('layouts.app');

?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="{ showToast: false, toastText: '' }">
    <div class="flex items-start justify-between gap-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Your Cart</h1>
            <p class="mt-1 text-sm text-gray-600">Review items by vendor and adjust quantities before checkout.</p>
        </div>

        <a href="{{ route('market.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
            Continue shopping
        </a>
    </div>

    @if ($warning)
        <div class="mt-6 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            {{ $warning }}
        </div>
    @endif

    @if ($notice)
        <div class="mt-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
            {{ $notice }}
        </div>
    @endif

    <div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            @if (count($this->groupedItems) === 0)
                <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-200 p-10 text-center">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-gray-100">
                        <svg class="h-6 w-6 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.5l1.5 15h13.5l2.25-9H6" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 21a.75.75 0 100-1.5A.75.75 0 009 21zm9 0a.75.75 0 100-1.5A.75.75 0 0018 21z" />
                        </svg>
                    </div>
                    <div class="mt-4 text-base font-medium text-gray-900">Your cart is empty</div>
                    <div class="mt-1 text-sm text-gray-600">Add a few items from the marketplace to get started.</div>
                    <div class="mt-5">
                        <a href="{{ route('market.index') }}" class="inline-flex items-center rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">
                            Browse products
                        </a>
                    </div>
                </div>
            @else
                @foreach ($this->groupedItems as $vendorGroup)
                    <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-200 overflow-hidden">
                        <div class="flex items-center justify-between px-4 py-3 bg-gray-50">
                            <div class="min-w-0">
                                <div class="text-sm font-semibold text-gray-900 truncate">
                                    {{ $vendorGroup['vendor_name'] }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ count($vendorGroup['items']) }} items
                                </div>
                            </div>
                            <div class="text-sm font-semibold text-gray-900">
                                ${{ number_format((float) $vendorGroup['subtotal'], 2) }}
                            </div>
                        </div>

                        <div class="divide-y divide-gray-100">
                            @foreach ($vendorGroup['items'] as $item)
                                <div class="p-4 flex gap-4">
                                    <div class="h-20 w-20 rounded-lg bg-gray-100 overflow-hidden shrink-0 ring-1 ring-gray-200">
                                        @if ($item['image_url'])
                                            <img
                                                src="{{ $item['image_url'] }}"
                                                alt="{{ $item['product_name'] }}"
                                                class="h-full w-full object-cover"
                                                loading="lazy"
                                                onerror="this.onerror=null;this.remove();"
                                            />
                                        @else
                                            <div class="h-full w-full flex items-center justify-center text-xs text-gray-500">No image</div>
                                        @endif
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-start justify-between gap-4">
                                            <div class="min-w-0">
                                                <div class="text-sm font-semibold text-gray-900 truncate">
                                                    {{ $item['product_name'] }}
                                                </div>
                                                <div class="mt-1 text-xs text-gray-500">
                                                    ${{ number_format((float) $item['price'], 2) }} each · Stock {{ $item['stock'] }}
                                                </div>
                                            </div>

                                            <div class="text-right">
                                                <div class="text-sm font-semibold text-gray-900">
                                                    ${{ number_format((float) $item['line_total'], 2) }}
                                                </div>
                                                <div class="mt-1 text-xs text-gray-500">
                                                    Line total
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mt-3 flex items-center justify-between gap-4">
                                            <div
                                                class="inline-flex items-center rounded-md border border-gray-200 bg-white shadow-sm"
                                                x-data="{ qty: {{ (int) $item['quantity'] }}, stock: {{ (int) $item['stock'] }} }"
                                            >
                                                <button
                                                    type="button"
                                                    class="px-3 py-2 text-gray-700 hover:bg-gray-50 disabled:opacity-50"
                                                    :disabled="qty <= 1"
                                                    x-on:click="qty = Math.max(1, qty - 1); $wire.updateQuantity('{{ $item['id'] }}', qty)"
                                                >
                                                    <span class="sr-only">Decrease</span>
                                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                        <path d="M5 10a.75.75 0 01.75-.75h8.5a.75.75 0 010 1.5h-8.5A.75.75 0 015 10z" />
                                                    </svg>
                                                </button>

                                                <input
                                                    class="w-14 border-0 text-center text-sm font-medium text-gray-900 focus:ring-0"
                                                    type="number"
                                                    min="1"
                                                    step="1"
                                                    x-model="qty"
                                                    x-on:change="qty = Math.max(1, parseInt(qty || 1)); $wire.updateQuantity('{{ $item['id'] }}', qty)"
                                                />

                                                <button
                                                    type="button"
                                                    class="px-3 py-2 text-gray-700 hover:bg-gray-50 disabled:opacity-50"
                                                    :disabled="qty >= stock"
                                                    x-on:click="qty = Math.min(stock, qty + 1); $wire.updateQuantity('{{ $item['id'] }}', qty)"
                                                >
                                                    <span class="sr-only">Increase</span>
                                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                        <path d="M10 4.25a.75.75 0 01.75.75v4.25H15a.75.75 0 010 1.5h-4.25V15a.75.75 0 01-1.5 0v-4.25H5a.75.75 0 010-1.5h4.25V5a.75.75 0 01.75-.75z" />
                                                    </svg>
                                                </button>
                                            </div>

                                            <button
                                                type="button"
                                                wire:click="remove('{{ $item['id'] }}')"
                                                class="text-sm font-medium text-red-600 hover:text-red-700"
                                            >
                                                Remove
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @endif
        </div>

        <div class="h-fit rounded-xl bg-white shadow-sm ring-1 ring-gray-200 p-4 lg:sticky lg:top-6">
            <div class="flex items-center justify-between">
                <div class="text-sm font-semibold text-gray-900">Summary</div>
                <div class="text-xs text-gray-500">
                    {{ count($this->groupedItems) }} vendors
                </div>
            </div>

            <div class="mt-3 space-y-2 text-sm">
                <div class="flex items-center justify-between">
                    <div class="text-gray-600">Total</div>
                    <div class="font-semibold text-gray-900">${{ number_format((float) $this->grandTotal, 2) }}</div>
                </div>
            </div>

            <a
                href="{{ route('checkout.index') }}"
                class="mt-4 inline-flex w-full items-center justify-center rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800"
            >
                Proceed to checkout
            </a>

            <div class="mt-3 text-xs text-gray-500">
                Checkout validates stock and rejects totals over $999.
            </div>
        </div>
    </div>
</div>

