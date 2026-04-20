<?php

use App\Domain\Cart\Actions\RemoveFromCartAction;
use App\Domain\Cart\Actions\UpdateCartItemQuantityAction;
use App\Domain\Cart\DTOs\RemoveFromCartDTO;
use App\Domain\Cart\DTOs\UpdateCartItemQuantityDTO;
use App\Domain\Cart\Models\Cart;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public ?string $warning = null;

    public function remove(string $cartItemId): void
    {
        $userId = (string) Auth::id();

        app(RemoveFromCartAction::class)->execute(new RemoveFromCartDTO(
            userId: $userId,
            cartItemId: $cartItemId,
        ));

        $this->warning = null;
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
}->layout('layouts.app');

?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-start justify-between gap-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Your Cart</h1>
            <p class="mt-1 text-sm text-gray-600">Items are grouped by vendor.</p>
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

    <div class="mt-6 space-y-6">
        @if (count($this->groupedItems) === 0)
            <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-200 p-8 text-center">
                <div class="text-base font-medium text-gray-900">Your cart is empty</div>
                <div class="mt-1 text-sm text-gray-600">Add a few items from the marketplace.</div>
                <div class="mt-4">
                    <a href="{{ route('market.index') }}" class="inline-flex items-center rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">
                        Browse products
                    </a>
                </div>
            </div>
        @else
            @foreach ($this->groupedItems as $vendorGroup)
                <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-200 overflow-hidden">
                    <div class="flex items-center justify-between px-4 py-3 bg-gray-50">
                        <div class="text-sm font-semibold text-gray-900">
                            {{ $vendorGroup['vendor_name'] }}
                        </div>
                        <div class="text-sm font-semibold text-gray-900">
                            Subtotal: ${{ number_format((float) $vendorGroup['subtotal'], 2) }}
                        </div>
                    </div>

                    <div class="divide-y divide-gray-100">
                        @foreach ($vendorGroup['items'] as $item)
                            <div class="p-4 flex gap-4">
                                <div class="h-20 w-20 rounded-lg bg-gray-100 overflow-hidden shrink-0">
                                    @if ($item['image_url'])
                                        <img src="{{ $item['image_url'] }}" alt="{{ $item['product_name'] }}" class="h-full w-full object-cover" loading="lazy" />
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
                                                ${{ number_format((float) $item['price'], 2) }} each · Stock: {{ $item['stock'] }}
                                            </div>
                                        </div>

                                        <div class="text-sm font-semibold text-gray-900">
                                            ${{ number_format((float) $item['line_total'], 2) }}
                                        </div>
                                    </div>

                                    <div class="mt-3 flex items-center justify-between gap-4">
                                        <div class="flex items-center gap-3">
                                            <label class="text-xs font-medium text-gray-700" for="qty-{{ $item['id'] }}">Qty</label>
                                            <input
                                                id="qty-{{ $item['id'] }}"
                                                type="number"
                                                min="1"
                                                step="1"
                                                value="{{ $item['quantity'] }}"
                                                class="w-24 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                wire:change="updateQuantity('{{ $item['id'] }}', $event.target.value)"
                                            />
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

            <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-200 p-4 flex items-center justify-between">
                <div class="text-sm text-gray-600">
                    Total
                </div>
                <div class="text-lg font-semibold text-gray-900">
                    ${{ number_format((float) $this->grandTotal, 2) }}
                </div>
            </div>
        @endif
    </div>
</div>

