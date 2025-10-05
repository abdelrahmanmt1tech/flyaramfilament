<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountTax extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'ticket_id',
        'type',
        'tax_percentage',
        'tax_value',
        'tax_types_id',
        'is_returned',
        'zakah_id',
        'zakah_response',
        'zakah_status',
        
    ];

    protected $casts = [
        'is_returned' => 'boolean',
        'zakah_response' => 'array',
        'tax_percentage' => 'decimal:2',
        'tax_value' => 'decimal:2',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function taxType(): BelongsTo
    {
        return $this->belongsTo(TaxType::class, 'tax_types_id');
    }
}
