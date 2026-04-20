<?php

use App\Domain\ProductCatalog\Models\Vendor;
use App\Domain\ProductCatalog\Services\MarketplaceSearchService;
use App\Domain\Cart\Actions\AddToCartAction;
use App\Domain\Cart\DTOs\AddToCartDTO;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use function Livewire\Volt\layout;

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

    public function addToCart(string $productId): void
    {
        if (! Auth::check()) {
            $this->redirectRoute('login', navigate: true);

            return;
        }

        $result = app(AddToCartAction::class)->execute(new AddToCartDTO(
            userId: (string) Auth::id(),
            productId: $productId,
            quantity: 1,
        ));

        if (! $result->allowed) {
            $this->dispatch('toast', text: $result->warning ?? 'Unable to add to cart.');

            return;
        }

        $this->dispatch('toast', text: $result->warning ?? 'Added to cart.');
        $this->dispatch('cart-updated');
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
};

layout('layouts.app');

?>

<div
    class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8"
    x-data="{ filtersOpen: false, toastOpen: false, toastText: '' }"
    x-on:toast="toastText = $event.detail.text; toastOpen = true; setTimeout(() => toastOpen = false, 2200)"
>
    <div class="fixed inset-0 z-40 lg:hidden" x-show="filtersOpen" x-transition.opacity>
        <div class="absolute inset-0 bg-black/30" @click="filtersOpen = false"></div>
        <div class="absolute inset-y-0 right-0 w-full max-w-sm bg-white shadow-xl p-4 overflow-y-auto">
            <div class="flex items-center justify-between">
                <div class="text-sm font-semibold text-gray-900">Filters</div>
                <button type="button" class="rounded-md p-2 text-gray-600 hover:bg-gray-50" @click="filtersOpen = false">
                    <span class="sr-only">Close</span>
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>

            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700" for="keyword-mobile">Keyword</label>
                <input
                    id="keyword-mobile"
                    type="text"
                    wire:model.live.debounce.300ms="keyword"
                    placeholder="Search products…"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                />
            </div>

            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700" for="vendorId-mobile">Vendor</label>
                <select
                    id="vendorId-mobile"
                    wire:model.live="vendorId"
                    class="mt-1 block w-full rounded-md border-gray-300 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
                    <option value="">All vendors</option>
                    @foreach ($vendors as $vendor)
                        <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mt-4 grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="minPrice-mobile">Min</label>
                    <input
                        id="minPrice-mobile"
                        type="number"
                        step="0.01"
                        min="0"
                        wire:model.live="minPrice"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="maxPrice-mobile">Max</label>
                    <input
                        id="maxPrice-mobile"
                        type="number"
                        step="0.01"
                        min="0"
                        wire:model.live="maxPrice"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    />
                </div>
            </div>

            <div class="mt-4 flex items-center justify-between">
                <button
                    type="button"
                    wire:click="resetFilters"
                    class="inline-flex items-center rounded-md bg-gray-900 px-3 py-2 text-sm font-medium text-white hover:bg-gray-800"
                >
                    Reset
                </button>
                <button
                    type="button"
                    class="rounded-md border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                    @click="filtersOpen = false"
                >
                    Show results
                </button>
            </div>
        </div>
    </div>

    <div class="flex items-start justify-between gap-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Marketplace</h1>
            <p class="mt-1 text-sm text-gray-600">Find products, filter by vendor, and add to your cart.</p>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-3">
            <div class="flex items-center justify-between lg:hidden">
                <div class="text-sm font-semibold text-gray-900">Filters</div>
                <button type="button" class="rounded-md border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50" @click="filtersOpen = true">
                    Open
                </button>
            </div>

            <div class="hidden lg:block rounded-xl bg-white shadow-sm ring-1 ring-gray-200 p-4">
                <div class="text-sm font-semibold text-gray-900">Filters</div>

                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700" for="keyword">Keyword</label>
                    <div class="mt-1 relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 104.473 8.703l2.662 2.662a.75.75 0 101.06-1.06l-2.662-2.662A5.5 5.5 0 009 3.5zm-4 5.5a4 4 0 118 0 4 4 0 01-8 0z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <input
                            id="keyword"
                            type="text"
                            wire:model.live.debounce.300ms="keyword"
                            placeholder="Search products…"
                            class="block w-full rounded-md border-gray-300 pl-9 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        />
                    </div>
                </div>

                <div class="mt-4">
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

                <div class="mt-4 grid grid-cols-2 gap-3">
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

                <div class="mt-4 flex items-center justify-between">
                    <div class="text-xs text-gray-500">
                        {{ $this->products->total() }} results
                    </div>
                    <button
                        type="button"
                        wire:click="resetFilters"
                        class="inline-flex items-center rounded-md bg-gray-900 px-3 py-2 text-xs font-medium text-white hover:bg-gray-800"
                    >
                        Reset
                    </button>
                </div>
            </div>
        </div>

        <div class="lg:col-span-9">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-600">
                    Showing <span class="font-medium text-gray-900">{{ $this->products->total() }}</span> products
                </div>
            </div>

            <div class="mt-4">
        @if ($this->products->count() === 0)
            <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-200 p-8 text-center">
                <div class="text-base font-medium text-gray-900">No products found</div>
                <div class="mt-1 text-sm text-gray-600">Try adjusting your filters.</div>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                @foreach ($this->products as $product)
                    <div class="group h-full rounded-xl bg-white shadow-sm ring-1 ring-gray-200 overflow-hidden hover:shadow-md transition flex flex-col">
                        <div class="aspect-square bg-gray-100 relative">
                            @if ($product->image_url)
                                <img
                                    src="{{ $product->image_url }}"
                                    alt="{{ $product->name }}"
                                    class="h-full w-full object-cover"
                                    loading="lazy"
                                    onerror="this.onerror=null;this.remove();"
                                />
                            @else
                                <div class="h-full w-full flex items-center justify-center text-sm text-gray-500">
                                    No image
                                </div>
                            @endif

                            <div class="absolute top-3 left-3">
                                <span class="inline-flex items-center rounded-full bg-white/90 px-2 py-1 text-xs font-medium text-gray-900 ring-1 ring-gray-200">
                                    {{ $product->vendor?->name }}
                                </span>
                            </div>
                        </div>

                        <div class="p-4 flex flex-col flex-1">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="text-sm font-semibold text-gray-900 line-clamp-2">
                                        {{ $product->name }}
                                    </div>
                                    <div class="mt-1 text-xs text-gray-500">In stock: {{ $product->stock }}</div>
                                </div>
                                <div class="shrink-0 text-sm font-semibold text-gray-900">
                                    ${{ number_format((float) $product->price, 2) }}
                                </div>
                            </div>

                            <div class="mt-auto pt-4 flex items-center gap-3">
                                <button
                                    type="button"
                                    wire:click="addToCart('{{ $product->id }}')"
                                    wire:loading.attr="disabled"
                                    @disabled((int) $product->stock <= 0)
                                    class="inline-flex w-full items-center justify-center rounded-md bg-gray-900 px-3 py-2 text-sm font-medium text-white hover:bg-gray-800 disabled:opacity-40 disabled:cursor-not-allowed"
                                >
                                    @if ((int) $product->stock <= 0)
                                        Out of stock
                                    @else
                                        Add to cart
                                    @endif
                                </button>
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
    </div>

    <div class="fixed bottom-4 right-4 z-50" x-show="toastOpen" x-transition.opacity>
        <div class="rounded-lg bg-gray-900 text-white text-sm px-4 py-3 shadow-lg">
            <span x-text="toastText"></span>
        </div>
    </div>
</div>

