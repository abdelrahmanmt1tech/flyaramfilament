<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Branch extends Model
{
    use SoftDeletes;
    use HasTranslations;

    public array $translatable = ['name'];

    protected $fillable = [
        'code',
        'name',
        'tax_number',
    ];

    public function classifications()
    {
        return $this->morphToMany(Classification::class, 'classifiable');
    }


    public function contactInfos()
    {
        return $this->morphMany(\App\Models\ContactInfo::class, 'contactable');
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
