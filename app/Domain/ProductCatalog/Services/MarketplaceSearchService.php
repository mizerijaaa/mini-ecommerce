<?php

namespace App\Domain\ProductCatalog\Services;

use App\Domain\ProductCatalog\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class MarketplaceSearchService
{
    /**
     * @param  array{
     *   keyword?: string|null,
     *   vendor_id?: string|null,
     *   min_price?: float|int|string|null,
     *   max_price?: float|int|string|null,
     * }  $filters
     */
    public function query(array $filters = []): Builder
    {
        $keyword = isset($filters['keyword']) ? trim((string) $filters['keyword']) : null;
        $vendorId = $filters['vendor_id'] ?? null;
        $minPrice = $filters['min_price'] ?? null;
        $maxPrice = $filters['max_price'] ?? null;

        $query = Product::query()
            ->with('vendor')
            ->active()
            ->when($vendorId, fn (Builder $q) => $q->forVendor((string) $vendorId))
            ->priceBetween($minPrice, $maxPrice);

        if ($keyword !== null && $keyword !== '') {
            $query->where(function (Builder $q) use ($keyword): void {
                $q->where('name', 'like', '%'.$keyword.'%')
                    ->orWhere('description', 'like', '%'.$keyword.'%');
            });
        }

        return $query->orderByDesc('created_at');
    }

    /**
     * @param  array{
     *   keyword?: string|null,
     *   vendor_id?: string|null,
     *   min_price?: float|int|string|null,
     *   max_price?: float|int|string|null,
     * }  $filters
     */
    public function paginate(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        return $this->query($filters)->paginate($perPage);
    }
}
