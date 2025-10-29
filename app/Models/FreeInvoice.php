<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FreeInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'beneficiary_name',
        'beneficiary_address',
        'beneficiary_tax_number',
        'beneficiary_phone',
        'beneficiary_email',
        'items',
        'total',
        'issue_date',
        'due_date',
        'free_invoiceable_type',
        'free_invoiceable_id',
        'tax_type_id'

    ];

    protected $casts = [
        'items' => 'array',
        'issue_date' => 'date',
        'due_date' => 'date',
        'total' => 'decimal:2',
    ];

    public function freeInvoiceable()
    {
        return $this->morphTo();
    }

    // protected static function booted()
    // {
    //     static::saving(function ($invoice) {
    //         if (is_array($invoice->items)) {
    //             $invoice->total = collect($invoice->items)->sum(function ($item) {
    //                 return ($item['quantity'] ?? 0) * ($item['price'] ?? 0);
    //             });
    //         }
    //     });
    // }
    protected static function booted()
    {
        static::saving(function ($invoice) {
            $subtotal = 0;

            if (is_array($invoice->items)) {
                $subtotal = collect($invoice->items)->sum(function ($item) {
                    return ($item['quantity'] ?? 0) * ($item['price'] ?? 0);
                });
            }

            $taxAmount = 0;
            if ($invoice->tax_type_id) {
                $taxType = TaxType::find($invoice->tax_type_id);
                if ($taxType) {
                    $taxValue = (float) $taxType->value; 
                    $taxAmount = $subtotal * ($taxValue / 100);
                }
            }

            $invoice->total = $subtotal + $taxAmount;
        });
    }

    public function taxType()
    {
        return $this->belongsTo(TaxType::class);
    }
}
