<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HalkhataHistory extends Model
{
    protected $fillable = [
        'customer_id',
        'fiscal_year',
        'opening_due',
        'closing_due',
        'total_paid_in_year',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
