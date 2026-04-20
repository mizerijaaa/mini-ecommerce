<?php

use App\Domain\ProductCatalog\Actions\CreateProductAction;
use App\Domain\ProductCatalog\Actions\DeleteProductAction;
use App\Domain\ProductCatalog\Actions\UpdateProductAction;
use App\Domain\ProductCatalog\DTOs\CreateProductDTO;
use App\Domain\ProductCatalog\DTOs\DeleteProductDTO;
use App\Domain\ProductCatalog\DTOs\UpdateProductDTO;
use App\Domain\ProductCatalog\Enums\ProductStatus;
use App\Domain\ProductCatalog\Models\Product;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use function Livewire\Volt\layout;

new class extends Component {
    use WithPagination;

    public ?string $message = null;
    public ?string $error = null;

    public string $name = '';
    public string $description = '';
    public string $price = '';
    public string $stock = '0';
    public ?string $imageUrl = null;
    public string $status = ProductStatus::Draft->value;

    public function createProduct(): void
    {
        $this->message = null;
        $this->error = null;

        $userId = (string) Auth::id();

        $name = trim($this->name);
        $description = trim($this->description);
        $price = (float) $this->price;
        $stock = (int) $this->stock;
        $status = $this->status;

        if ($name === '' || $description === '') {
            $this->error = 'Name and description are required.';

            return;
        }

        if (! in_array($status, $this->statuses, true)) {
            $this->error = 'Invalid status.';

            return;
        }

        if ($price <= 0) {
            $this->error = 'Price must be greater than 0.';

            return;
        }

        app(CreateProductAction::class)->execute(new CreateProductDTO(
            userId: $userId,
            name: $name,
            description: $description,
            price: $price,
            stock: max(0, $stock),
            imageUrl: $this->imageUrl ? trim((string) $this->imageUrl) : null,
            status: $status,
        ));

        $this->reset(['name', 'description', 'price', 'stock', 'imageUrl', 'status']);
        $this->status = ProductStatus::Draft->value;
        $this->message = 'Product created.';
        $this->resetPage();
    }

    public function updateProduct(string $productId, array $payload): void
    {
        $this->message = null;
        $this->error = null;
        $userId = (string) Auth::id();

        app(UpdateProductAction::class)->execute(new UpdateProductDTO(
            userId: $userId,
            productId: $productId,
            status: $payload['status'] ?? null,
            stock: array_key_exists('stock', $payload) ? (int) $payload['stock'] : null,
        ));

        $this->message = 'Product updated.';
    }

    public function deleteProduct(string $productId): void
    {
        $this->message = null;
        $this->error = null;

        $userId = (string) Auth::id();

        app(DeleteProductAction::class)->execute(new DeleteProductDTO(
            userId: $userId,
            productId: $productId,
        ));

        $this->message = 'Product deleted.';
        $this->resetPage();
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
        return ProductStatus::values();
    }
};

layout('layouts.app');

?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-start justify-between gap-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Manage Products</h1>
            <p class="mt-1 text-sm text-gray-600">Create products, and update stock/status for your catalog.</p>
        </div>

        <a href="{{ route('vendor.orders.index') }}" class="text-sm font-medium text-gray-900 hover:text-gray-700">
            View vendor orders
        </a>
    </div>

    <div class="mt-6 rounded-xl bg-white shadow-sm ring-1 ring-gray-200 p-4">
        <div class="text-sm font-semibold text-gray-900">Create product</div>
        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Name</label>
                <input wire:model.live="name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Product name" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Status</label>
                <select wire:model.live="status" class="mt-1 block w-full rounded-md border-gray-300 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @foreach ($this->statuses as $s)
                        <option value="{{ $s }}">{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700">Description</label>
                <textarea wire:model.live="description" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Short description"></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Price</label>
                <input wire:model.live="price" type="number" min="0" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="19.99" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Stock</label>
                <input wire:model.live="stock" type="number" min="0" step="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700">Image URL (optional)</label>
                <input wire:model.live="imageUrl" type="url" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="https://..." />
            </div>
        </div>
        <div class="mt-4 flex items-center justify-end gap-3">
            <button
                type="button"
                wire:click="createProduct"
                wire:loading.attr="disabled"
                class="inline-flex items-center rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800 disabled:opacity-50 disabled:cursor-not-allowed"
            >
                Create
            </button>
        </div>
    </div>

    @if ($error)
        <div class="mt-6 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-900">
            {{ $error }}
        </div>
    @endif

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
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600">Actions</th>
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
                                    <div class="inline-flex items-center gap-2">
                                        <button
                                            type="button"
                                            class="inline-flex items-center rounded-md bg-gray-900 px-3 py-2 text-sm font-medium text-white hover:bg-gray-800"
                                            x-on:click="$wire.updateProduct('{{ $product->id }}', { stock, status })"
                                        >
                                            Save
                                        </button>
                                        <button
                                            type="button"
                                            class="inline-flex items-center rounded-md border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-red-600 hover:bg-gray-50"
                                            x-on:click="confirm('Delete this product?') && $wire.deleteProduct('{{ $product->id }}')"
                                        >
                                            Delete
                                        </button>
                                    </div>
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

