<?php

use App\Domain\ProductCatalog\Actions\UpdateProductAction;
use App\Domain\ProductCatalog\DTOs\UpdateProductDTO;
use App\Domain\ProductCatalog\Models\Product;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use function Livewire\Volt\layout;

new class extends Component {
    use WithPagination;

    public ?string $message = null;

    public function updateProduct(string $productId, array $payload): void
    {
        $userId = (string) Auth::id();

        app(UpdateProductAction::class)->execute(new UpdateProductDTO(
            userId: $userId,
            productId: $productId,
            status: $payload['status'] ?? null,
            stock: array_key_exists('stock', $payload) ? (int) $payload['stock'] : null,
        ));

        $this->message = 'Product updated.';
    }

    public function getProductsProperty()
    {
        $vendorId = (string) (Auth::user()?->vendor?->id ?? '');

        if ($vendorId === '') {
            abort(403);
        }

        return Product::query()
            ->where('vendor_id', $vendorId)
            ->orderByDesc('created_at')
            ->paginate(12);
    }

    public function getStatusesProperty(): array
    {
        return ['draft', 'active', 'archived'];
    }
};

layout('layouts.app');

?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-start justify-between gap-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Manage Products</h1>
            <p class="mt-1 text-sm text-gray-600">Update stock and status for your products.</p>
        </div>

        <a href="{{ route('vendor.orders.index') }}" class="text-sm font-medium text-gray-900 hover:text-gray-700">
            View vendor orders
        </a>
    </div>

    @if ($message)
        <div class="mt-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
            {{ $message }}
        </div>
    @endif

    <div class="mt-6 rounded-xl bg-white shadow-sm ring-1 ring-gray-200 overflow-hidden">
        @if ($this->products->count() === 0)
            <div class="p-8 text-center">
                <div class="text-base font-medium text-gray-900">No products yet</div>
                <div class="mt-1 text-sm text-gray-600">Seeded vendors should have products. If not, reseed.</div>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Product</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Price</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Stock</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600">Save</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($this->products as $product)
                            <tr x-data="{ stock: {{ (int) $product->stock }}, status: '{{ $product->status }}' }">
                                <td class="px-4 py-3 text-sm text-gray-900">
                                    <div class="font-medium line-clamp-2">
                                        {{ $product->name }}
                                    </div>
                                    <div class="mt-1 text-xs text-gray-500">
                                        #{{ $product->id }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900">
                                    ${{ number_format((float) $product->price, 2) }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900">
                                    <input
                                        type="number"
                                        min="0"
                                        step="1"
                                        class="w-28 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        x-model="stock"
                                    />
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900">
                                    <select
                                        class="w-40 rounded-md border-gray-300 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        x-model="status"
                                    >
                                        @foreach ($this->statuses as $status)
                                            <option value="{{ $status }}">{{ $status }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <button
                                        type="button"
                                        class="inline-flex items-center rounded-md bg-gray-900 px-3 py-2 text-sm font-medium text-white hover:bg-gray-800"
                                        x-on:click="$wire.updateProduct('{{ $product->id }}', { stock, status })"
                                    >
                                        Save
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-4">
                {{ $this->products->links() }}
            </div>
        @endif
    </div>
</div>

