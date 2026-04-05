<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarcodeHistory extends Model
{
    use HasFactory;

    protected $table = 'barcode_history';

    protected $fillable = [
        'product_variant_id',
        'old_barcode',
        'new_barcode',
        'changed_by',
    ];

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
