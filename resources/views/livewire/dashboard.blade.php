<?php

use App\Domain\IdentityAndAccess\Models\User;
use App\Domain\OrderManagement\Models\Order;
use App\Domain\OrderManagement\Models\OrderItem;
use App\Domain\ProductCatalog\Models\Product;
use App\Domain\ProductCatalog\Models\Vendor;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use function Livewire\Volt\layout;

new class extends Component {
    public function with(): array
    {
        $user = Auth::user();

        if (! $user) {
            return [];
        }

        if ($user->isAdmin()) {
            return [
                'adminStats' => [
                    'users' => User::query()->count(),
                    'vendors' => Vendor::query()->count(),
                    'products' => Product::query()->count(),
                    'orders' => Order::query()->count(),
                ],
            ];
        }

        if ($user->isVendor()) {
            $vendorId = (string) ($user->vendor?->id ?? '');

            $recentItems = $vendorId === ''
                ? collect()
                : OrderItem::query()
                    ->where('vendor_id', $vendorId)
                    ->with(['order.user', 'product'])
                    ->orderByDesc('created_at')
                    ->limit(5)
                    ->get();

            return [
                'vendorStats' => [
                    'products' => $vendorId === '' ? 0 : Product::query()->where('vendor_id', $vendorId)->count(),
                ],
                'recentOrderItems' => $recentItems,
            ];
        }

        return [];
    }
};

layout('layouts.app');

?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-start justify-between gap-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Dashboard</h1>
            <p class="mt-1 text-sm text-gray-600">Quick overview of your account.</p>
        </div>
    </div>

    @if (Auth::user()?->isAdmin())
        <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-200 p-4">
                <div class="text-xs font-medium text-gray-500">Users</div>
                <div class="mt-2 text-2xl font-semibold text-gray-900">{{ $adminStats['users'] }}</div>
            </div>
            <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-200 p-4">
                <div class="text-xs font-medium text-gray-500">Vendors</div>
                <div class="mt-2 text-2xl font-semibold text-gray-900">{{ $adminStats['vendors'] }}</div>
            </div>
            <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-200 p-4">
                <div class="text-xs font-medium text-gray-500">Products</div>
                <div class="mt-2 text-2xl font-semibold text-gray-900">{{ $adminStats['products'] }}</div>
            </div>
            <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-200 p-4">
                <div class="text-xs font-medium text-gray-500">Orders</div>
                <div class="mt-2 text-2xl font-semibold text-gray-900">{{ $adminStats['orders'] }}</div>
            </div>
        </div>

        <div class="mt-6 rounded-xl bg-white shadow-sm ring-1 ring-gray-200 p-6">
            <div class="text-sm font-semibold text-gray-900">Platform overview</div>
            <div class="mt-1 text-sm text-gray-600">
                Minimal health snapshot: users, vendors, products, and orders.
            </div>
        </div>
    @elseif (Auth::user()?->isVendor())
        <div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-200 p-6">
                <div class="text-sm font-semibold text-gray-900">Products</div>
                <div class="mt-2 text-3xl font-semibold text-gray-900">{{ $vendorStats['products'] }}</div>
                <div class="mt-4">
                    <a
                        href="{{ route('vendor.products.index') }}"
                        class="inline-flex items-center rounded-md bg-gray-900 px-3 py-2 text-sm font-medium text-white hover:bg-gray-800"
                    >
                        Manage products
                    </a>
                </div>
            </div>

            <div class="lg:col-span-2 rounded-xl bg-white shadow-sm ring-1 ring-gray-200 overflow-hidden">
                <div class="px-4 py-3 bg-gray-50 flex items-center justify-between">
                    <div class="text-sm font-semibold text-gray-900">Recent order items</div>
                    <a href="{{ route('vendor.orders.index') }}" class="text-sm font-medium text-gray-900 hover:text-gray-700">
                        View all
                    </a>
                </div>

                @if (($recentOrderItems ?? collect())->count() === 0)
                    <div class="p-6 text-sm text-gray-600">
                        No orders yet. When buyers purchase your products, they’ll appear here.
                    </div>
                @else
                    <div class="divide-y divide-gray-100">
                        @foreach ($recentOrderItems as $item)
                            @php
                                $lineTotal = (float) $item->price * (int) $item->quantity;
                            @endphp
                            <div class="p-4 flex items-start justify-between gap-6">
                                <div class="min-w-0">
                                    <div class="text-sm font-semibold text-gray-900 truncate">
                                        {{ $item->product?->name ?? '—' }}
                                    </div>
                                    <div class="mt-1 text-xs text-gray-500">
                                        Buyer: {{ $item->order?->user?->name ?? '—' }} · Qty {{ $item->quantity }} · Status {{ $item->status?->value ?? 'pending' }}
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-semibold text-gray-900">
                                        ${{ number_format($lineTotal, 2) }}
                                    </div>
                                    <div class="mt-2">
                                        <a
                                            href="{{ route('vendor.orders.index') }}"
                                            class="text-sm font-medium text-gray-900 hover:text-gray-700"
                                        >
                                            Update status
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @else
        <div class="mt-6 rounded-xl bg-white shadow-sm ring-1 ring-gray-200 p-6">
            <div class="text-sm font-semibold text-gray-900">Welcome</div>
            <div class="mt-1 text-sm text-gray-600">Browse the marketplace and place orders.</div>
            <div class="mt-4 flex items-center gap-3">
                <a href="{{ route('market.index') }}" class="inline-flex items-center rounded-md bg-gray-900 px-3 py-2 text-sm font-medium text-white hover:bg-gray-800">
                    Go to marketplace
                </a>
                <a href="{{ route('buyer.orders.index') }}" class="text-sm font-medium text-gray-900 hover:text-gray-700">
                    My orders
                </a>
            </div>
        </div>
    @endif
</div>

