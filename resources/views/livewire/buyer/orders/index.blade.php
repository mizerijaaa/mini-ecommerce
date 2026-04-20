<?php

use App\Domain\OrderManagement\Models\Order;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public function getOrdersProperty()
    {
        $userId = (string) Auth::id();

        return Order::query()
            ->where('user_id', $userId)
            ->with(['items.product.vendor'])
            ->orderByDesc('created_at')
            ->paginate(10);
    }
}->layout('layouts.app');

?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-start justify-between gap-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Your Orders</h1>
            <p class="mt-1 text-sm text-gray-600">Order history and status tracking.</p>
        </div>
    </div>

    <div class="mt-6 rounded-xl bg-white shadow-sm ring-1 ring-gray-200 overflow-hidden">
        @if ($this->orders->count() === 0)
            <div class="p-8 text-center">
                <div class="text-base font-medium text-gray-900">No orders yet</div>
                <div class="mt-1 text-sm text-gray-600">When you check out, your orders will appear here.</div>
                <div class="mt-4">
                    <a href="{{ route('market.index') }}" class="inline-flex items-center rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">
                        Browse marketplace
                    </a>
                </div>
            </div>
        @else
            <div class="divide-y divide-gray-100">
                @foreach ($this->orders as $order)
                    @php
                        $total = (float) $order->items->sum(fn ($i) => (float) $i->price * (int) $i->quantity);
                    @endphp

                    <a href="{{ route('buyer.orders.show', ['orderId' => $order->id]) }}" class="block p-4 hover:bg-gray-50">
                        <div class="flex items-center justify-between gap-6">
                            <div class="min-w-0">
                                <div class="text-sm font-semibold text-gray-900">
                                    Order #{{ $order->id }}
                                </div>
                                <div class="mt-1 text-xs text-gray-500">
                                    Placed {{ $order->created_at?->format('M j, Y g:i A') }}
                                    · {{ $order->items->count() }} items
                                    · Payment: {{ $order->payment_method }}
                                </div>
                            </div>

                            <div class="text-right">
                                <div class="text-sm font-semibold text-gray-900">
                                    ${{ number_format($total, 2) }}
                                </div>
                                <div class="mt-1 text-xs text-gray-600">
                                    Status: {{ $order->status->value }}
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="p-4">
                {{ $this->orders->links() }}
            </div>
        @endif
    </div>
</div>

