<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'address',
        'village',
        'nid',
        'credit_limit',
        'risk_level',
        'previous_due',
    ];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function ledgers()
    {
        return $this->hasMany(CustomerLedger::class);
    }

    public function halkhataHistories()
    {
        return $this->hasMany(HalkhataHistory::class);
    }

    public function updateBalance($amount, $type = 'debit')
    {
        if ($type === 'debit') {
            $this->previous_due += $amount;
        } else {
            $this->previous_due -= $amount;
        }
        $this->save();
        return $this->previous_due;
    }
}
