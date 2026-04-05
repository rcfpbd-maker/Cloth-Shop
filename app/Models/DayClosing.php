<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DayClosing extends Model
{
    protected $table = 'day_closing';

    protected $fillable = [
        'closing_date',
        'opening_cash',
        'total_sales',
        'total_expense',
        'total_collection',
        'closing_cash',
        'closed_by',
    ];

    public function closedBy()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }
}
