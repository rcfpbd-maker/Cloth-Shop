<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    protected $fillable = [
        'purchase_id',
        'product_variant_id',
        'quantity',
        'price',
        'discount',
        'total',
    ];

    public $timestamps = false;

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
