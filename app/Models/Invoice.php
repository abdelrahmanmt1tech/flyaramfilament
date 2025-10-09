<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{

    protected $fillable = [
        'type',
        'is_drafted',
        'total_taxes',
        'total_amount',
        'invoice_number',
        'due_date',
        'notes',
        'invoiceable_type',
        'invoiceable_id',
        'reference_num',
        'reservation_id'
    ];

    protected $casts = [
        'is_drafted' => 'boolean',
        'due_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function invoiceable()
    {
        return $this->morphTo();
    }

    public function tickets()
    {
        return $this->belongsToMany(Ticket::class);
    }

    public function refundInvoices()
    {
        return $this->hasMany(Invoice::class, 'reference_num', 'invoice_number')
            ->where('type', 'refund');
    }

    public function originalInvoice()
    {
        return $this->belongsTo(Invoice::class, 'reference_num', 'invoice_number')
            ->where('type', '!=', 'refund');
    }

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }
}
