<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable = [
        'supplier_id',
        'invoice_no',
        'purchase_date',
        'total_amount',
        'discount',
        'paid_amount',
        'due_amount',
        'payment_method_id',
        'created_by',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function payments()
    {
        return $this->morphMany(Payment::class, 'reference', 'reference_type', 'reference_id');
    }

    public function returns()
    {
        return $this->hasMany(ReturnItem::class);
    }
}
