<?php

namespace App\Domain\ProductCatalog\Models;

use App\Domain\Cart\Models\CartItem;
use App\Domain\OrderManagement\Models\OrderItem;
use App\Domain\ProductCatalog\Enums\ProductStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['vendor_id', 'name', 'description', 'price', 'stock', 'image_url', 'status'])]
class Product extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', ProductStatus::Active);
    }

    public function scopeForVendor(Builder $query, string|Vendor $vendor): Builder
    {
        $vendorId = $vendor instanceof Vendor ? $vendor->id : $vendor;

        return $query->where('vendor_id', $vendorId);
    }

    public function scopePriceBetween(Builder $query, float|int|string|null $min, float|int|string|null $max): Builder
    {
        if ($min !== null && $min !== '') {
            $query->where('price', '>=', (float) $min);
        }

        if ($max !== null && $max !== '') {
            $query->where('price', '<=', (float) $max);
        }

        return $query;
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'stock' => 'integer',
            'status' => ProductStatus::class,
        ];
    }
}
