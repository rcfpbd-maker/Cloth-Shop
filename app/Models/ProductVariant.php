<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariant extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'product_id',
        'size',
        'color',
        'purchase_price',
        'sale_price',
        'wholesale_price',
        'minimum_sale_price',
        'stock_quantity',
        'reorder_level',
        'stock_adjustment_note',
        'sku',
        'barcode',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Accessors
    public function getProfitAttribute()
    {
        return $this->sale_price - $this->purchase_price;
    }

    public function getIsLowStockAttribute()
    {
        return $this->stock_quantity <= $this->reorder_level;
    }

    // Scopes
    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock_quantity', '<=', 'reorder_level');
    }

    public function inventory()
    {
        return $this->hasOne(Inventory::class);
    }

    public function sales()
    {
        return $this->hasManyThrough(Sale::class, SaleItem::class, 'product_variant_id', 'id', 'id', 'sale_id');
    }
}
