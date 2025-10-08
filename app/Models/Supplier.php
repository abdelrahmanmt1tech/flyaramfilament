<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'tax_number',

    ];

    public function contactInfos()
    {
        return $this->morphMany(\App\Models\ContactInfo::class, 'contactable');
    }


    public function firstContact()
    {
        return $this->morph(\App\Models\ContactInfo::class, 'contactable');
    }


    public function accountStatements()
    {
        return $this->morphMany(AccountStatement::class, 'statementable');
    }

    public function freeInvoice(): MorphOne
    {
        return $this->morphOne(FreeInvoice::class, 'free_invoiceable');
    }

    public function payments()
    {
        return $this->morphMany(Payment::class, 'paymentable');
    }
}
