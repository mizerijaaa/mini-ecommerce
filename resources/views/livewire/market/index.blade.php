<?php

use App\Domain\ProductCatalog\Models\Vendor;
use App\Domain\ProductCatalog\Services\MarketplaceSearchService;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $keyword = '';
    public string $vendorId = '';
    public string $minPrice = '';
    public string $maxPrice = '';

    public function updatingKeyword(): void
    {
        $this->resetPage();
    }

    public function updatingVendorId(): void
    {
        $this->resetPage();
    }

    public function updatingMinPrice(): void
    {
        $this->resetPage();
    }

    public function updatingMaxPrice(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->keyword = '';
        $this->vendorId = '';
        $this->minPrice = '';
        $this->maxPrice = '';
        $this->resetPage();
    }

    public function with(): array
    {
        return [
            'vendors' => Vendor::query()->orderBy('name')->get(['id', 'name']),
        ];
    }

    public function getProductsProperty()
    {
        return app(MarketplaceSearchService::class)->paginate(
            filters: [
                'keyword' => $this->keyword,
                'vendor_id' => $this->vendorId !== '' ? $this->vendorId : null,
                'min_price' => $this->minPrice !== '' ? $this->minPrice : null,
                'max_price' => $this->maxPrice !== '' ? $this->maxPrice : null,
            ],
            perPage: 12
        );
    }
}->layout('layouts.app');

?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-start justify-between gap-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Marketplace</h1>
            <p class="mt-1 text-sm text-gray-600">Browse active products across all vendors.</p>
        </div>

        <a href="{{ route('dashboard') }}" class="text-sm text-gray-600 hover:text-gray-900">
            Back to dashboard
        </a>
    </div>

    <div class="mt-6 rounded-xl bg-white shadow-sm ring-1 ring-gray-200 p-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700" for="keyword">Keyword</label>
                <input
                    id="keyword"
                    type="text"
                    wire:model.live.debounce.300ms="keyword"
                    placeholder="Search products…"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700" for="vendorId">Vendor</label>
                <select
                    id="vendorId"
                    wire:model.live="vendorId"
                    class="mt-1 block w-full rounded-md border-gray-300 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
                    <option value="">All vendors</option>
                    @foreach ($vendors as $vendor)
                        <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="minPrice">Min</label>
                    <input
                        id="minPrice"
                        type="number"
                        step="0.01"
                        min="0"
                        wire:model.live="minPrice"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="maxPrice">Max</label>
                    <input
                        id="maxPrice"
                        type="number"
                        step="0.01"
                        min="0"
                        wire:model.live="maxPrice"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    />
                </div>
            </div>
        </div>

        <div class="mt-4 flex items-center justify-between">
            <div class="text-sm text-gray-600">
                Showing <span class="font-medium text-gray-900">{{ $this->products->total() }}</span> products
            </div>

            <button
                type="button"
                wire:click="resetFilters"
                class="inline-flex items-center rounded-md bg-gray-900 px-3 py-2 text-sm font-medium text-white hover:bg-gray-800"
            >
                Reset
            </button>
        </div>
    </div>

    <div class="mt-6">
        @if ($this->products->count() === 0)
            <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-200 p-8 text-center">
                <div class="text-base font-medium text-gray-900">No products found</div>
                <div class="mt-1 text-sm text-gray-600">Try adjusting your filters.</div>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                @foreach ($this->products as $product)
                    <div class="group rounded-xl bg-white shadow-sm ring-1 ring-gray-200 overflow-hidden">
                        <div class="aspect-square bg-gray-100">
                            @if ($product->image_url)
                                <img
                                    src="{{ $product->image_url }}"
                                    alt="{{ $product->name }}"
                                    class="h-full w-full object-cover"
                                    loading="lazy"
                                />
                            @else
                                <div class="h-full w-full flex items-center justify-center text-sm text-gray-500">
                                    No image
                                </div>
                            @endif
                        </div>

                        <div class="p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="text-sm font-semibold text-gray-900 line-clamp-2">
                                        {{ $product->name }}
                                    </div>
                                    <div class="mt-1 text-xs text-gray-500">
                                        {{ $product->vendor?->name }}
                                    </div>
                                </div>
                                <div class="text-sm font-semibold text-gray-900">
                                    ${{ number_format((float) $product->price, 2) }}
                                </div>
                            </div>

                            <div class="mt-3 flex items-center justify-between">
                                <div class="text-xs text-gray-500">
                                    Stock: <span class="font-medium text-gray-700">{{ $product->stock }}</span>
                                </div>
                                <div class="text-xs text-gray-500">
                                    Added {{ $product->created_at?->diffForHumans() }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $this->products->links() }}
            </div>
        @endif
    </div>
</div>

