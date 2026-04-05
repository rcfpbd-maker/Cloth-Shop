<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'company_name',
        'previous_due',
        'status',
    ];

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function updateDue($amount)
    {
        $this->previous_due += $amount;
        $this->save();
    }
}
