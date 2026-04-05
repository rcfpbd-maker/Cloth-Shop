<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'type',
        'reference_id',
        'customer_id',
        'supplier_id',
        'total_amount',
        'discount',
        'paid_amount',
        'due_amount',
        'payment_method_id',
        'created_by',
    ];

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    /**
     * Get the source record (Sale, Purchase, or Return)
     */
    public function reference()
    {
        switch ($this->type) {
            case 'sale':
                return Sale::find($this->reference_id);
            case 'purchase':
                return Purchase::find($this->reference_id);
            case 'return':
                return ReturnItem::find($this->reference_id);
            default:
                return null;
        }
    }
}
