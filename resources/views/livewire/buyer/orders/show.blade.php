<?php

use App\Domain\OrderManagement\Models\Order;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use function Livewire\Volt\layout;

new class extends Component {
    public string $orderId;

    public function mount(string $orderId): void
    {
        $this->orderId = $orderId;
    }

    public function getOrderProperty(): Order
    {
        $userId = (string) Auth::id();

        return Order::query()
            ->whereKey($this->orderId)
            ->where('user_id', $userId)
            ->with(['items.product.vendor'])
            ->firstOrFail();
    }

    public function getGroupedItemsProperty(): array
    {
        $groups = [];

        foreach ($this->order->items as $item) {
            $vendorId = (string) ($item->vendor_id ?? 'unknown');
            $vendorName = $item->vendor?->name ?? $item->product?->vendor?->name ?? 'Unknown vendor';

            if (! array_key_exists($vendorId, $groups)) {
                $groups[$vendorId] = [
                    'vendor_name' => $vendorName,
                    'items' => [],
                    'subtotal' => 0.0,
                ];
            }

            $lineTotal = (float) $item->price * (int) $item->quantity;
            $groups[$vendorId]['items'][] = [
                'product_name' => $item->product?->name ?? 'Unknown product',
                'quantity' => (int) $item->quantity,
                'price' => (float) $item->price,
                'line_total' => $lineTotal,
                'status' => $item->status?->value ?? 'pending',
            ];
            $groups[$vendorId]['subtotal'] += $lineTotal;
        }

        return $groups;
    }

    public function getTotalProperty(): float
    {
        return (float) $this->order->items->sum(fn ($i) => (float) $i->price * (int) $i->quantity);
    }
};

layout('layouts.app');

?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-start justify-between gap-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Order Details</h1>
            <p class="mt-1 text-sm text-gray-600">Order #{{ $this->order->id }}</p>
        </div>

        <a href="{{ route('buyer.orders.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
            Back to orders
        </a>
    </div>

    <div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
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
                            <div class="p-4 flex items-start justify-between gap-6">
                                <div class="min-w-0">
                                    <div class="text-sm font-semibold text-gray-900 truncate">
                                        {{ $item['product_name'] }}
                                    </div>
                                    <div class="mt-1 text-xs text-gray-500">
                                        Qty {{ $item['quantity'] }} · ${{ number_format((float) $item['price'], 2) }} each
                                    </div>
                                    <div class="mt-1 text-xs text-gray-600">
                                        Item status: {{ $item['status'] }}
                                    </div>
                                </div>
                                <div class="text-sm font-semibold text-gray-900">
                                    ${{ number_format((float) $item['line_total'], 2) }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-200 p-4 h-fit">
            <div class="text-sm font-semibold text-gray-900">Summary</div>

            <div class="mt-3 space-y-2 text-sm">
                <div class="flex items-center justify-between">
                    <div class="text-gray-600">Status</div>
                    <div class="font-medium text-gray-900">{{ $this->order->status->value }}</div>
                </div>
                <div class="flex items-center justify-between">
                    <div class="text-gray-600">Payment method</div>
                    <div class="font-medium text-gray-900">{{ $this->order->payment_method }}</div>
                </div>
                <div class="flex items-center justify-between">
                    <div class="text-gray-600">Total</div>
                    <div class="font-semibold text-gray-900">${{ number_format((float) $this->total, 2) }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

