<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerLedger extends Model
{
    protected $fillable = [
        'customer_id',
        'type',
        'reference_type',
        'reference_id',
        'debit',
        'credit',
        'balance',
        'note',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function reference()
    {
        return $this->morphTo();
    }
}
