<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    
    protected $fillable = [
        'type', 'is_drafted', 'total_taxes', 'total_amount', 'invoice_number', 'notes', 'invoiceable_type', 'invoiceable_id',
    ];

    protected $casts = [
        'is_drafted' => 'boolean',
    ];

    public function invoiceable()
    {
        return $this->morphTo();
    }

    public function tickets()
    {
        return $this->belongsToMany(Ticket::class);
    }
}
