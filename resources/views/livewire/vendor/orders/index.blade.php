<?php

use App\Domain\OrderManagement\Actions\UpdateOrderItemStatusAction;
use App\Domain\OrderManagement\Enums\OrderStatus;
use App\Domain\OrderManagement\Models\OrderItem;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public ?string $message = null;

    public function updateItemStatus(string $orderItemId, string $status): void
    {
        $user = Auth::user();
        $vendorId = (string) ($user?->vendor?->id ?? '');

        if ($vendorId === '') {
            abort(403);
        }

        $to = OrderStatus::from($status);

        app(UpdateOrderItemStatusAction::class)->execute(
            vendorId: $vendorId,
            orderItemId: $orderItemId,
            to: $to,
        );

        $this->message = 'Status updated.';
    }

    public function getItemsProperty()
    {
        $user = Auth::user();
        $vendorId = (string) ($user?->vendor?->id ?? '');

        if ($vendorId === '') {
            abort(403);
        }

        return OrderItem::query()
            ->where('vendor_id', $vendorId)
            ->with(['order.user', 'product'])
            ->orderByDesc('created_at')
            ->paginate(15);
    }

    public function getStatusesProperty(): array
    {
        return array_map(fn (OrderStatus $s) => $s->value, OrderStatus::cases());
    }
}->layout('layouts.app');

?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-start justify-between gap-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Vendor Orders</h1>
            <p class="mt-1 text-sm text-gray-600">Orders that contain your products.</p>
        </div>
    </div>

    @if ($message)
        <div class="mt-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
            {{ $message }}
        </div>
    @endif

    <div class="mt-6 rounded-xl bg-white shadow-sm ring-1 ring-gray-200 overflow-hidden">
        @if ($this->items->count() === 0)
            <div class="p-8 text-center">
                <div class="text-base font-medium text-gray-900">No order items yet</div>
                <div class="mt-1 text-sm text-gray-600">When buyers purchase your products, they’ll appear here.</div>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Order</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Buyer</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Product</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Qty</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Price</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Line total</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Item status</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600">Update</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($this->items as $item)
                            @php
                                $lineTotal = (float) $item->price * (int) $item->quantity;
                                $current = $item->status ?? \App\Domain\OrderManagement\Enums\OrderStatus::Pending;
                            @endphp
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-900">
                                    <div class="font-medium">#{{ $item->order_id }}</div>
                                    <div class="text-xs text-gray-500">{{ $item->order?->created_at?->format('M j, Y') }}</div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900">
                                    {{ $item->order?->user?->name ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900">
                                    {{ $item->product?->name ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900">
                                    {{ $item->quantity }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900">
                                    ${{ number_format((float) $item->price, 2) }}
                                </td>
                                <td class="px-4 py-3 text-sm font-semibold text-gray-900">
                                    ${{ number_format($lineTotal, 2) }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900">
                                    <select
                                        class="w-44 rounded-md border-gray-300 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        wire:model.live="items.{{ $loop->index }}.status"
                                        x-data
                                        x-init="$el.value='{{ $current->value }}'"
                                        x-on:change="$wire.updateItemStatus('{{ $item->id }}', $event.target.value)"
                                    >
                                        @foreach ($this->statuses as $status)
                                            @php
                                                $allowed = $current->canTransitionTo(\App\Domain\OrderManagement\Enums\OrderStatus::from($status));
                                            @endphp
                                            <option value="{{ $status }}" @disabled(! $allowed)>
                                                {{ $status }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="text-xs text-gray-500">Forward-only</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-4">
                {{ $this->items->links() }}
            </div>
        @endif
    </div>
</div>

