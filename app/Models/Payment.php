<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_method',
        'amount',
        'payment_date',
        'notes',
        'account',
        'paymentable_id',
        'paymentable_type',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function paymentable()
    {
        return $this->morphTo();
    }
}
