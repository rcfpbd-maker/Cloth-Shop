<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'module',
        'action',
        'action_type',
        'reference_id',
        'description',
        'ip_address',
    ];

    public $timestamps = true; // Enabled for updated_at

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
