<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = ['category_id', 'sku', 'name', 'purchase_price', 'selling_price', 'stock', 'minimum_stock', 'description'];

    protected function casts(): array
    {
        return ['purchase_price' => 'decimal:2', 'selling_price' => 'decimal:2', 'stock' => 'integer', 'minimum_stock' => 'integer'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function isLowStock(): bool
    {
        return $this->stock <= $this->minimum_stock;
    }
}