<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Client extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'company_name',
        'phone',
        'tax_number',
        'sales_rep_id',
        'address',
        'email',
        'lead_source_id',
    ];

    public function salesRep(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sales_rep_id');
    }



    use HasTranslations;
    public $translatable = ['name','company_name','address'];

    public function leadSource()
    {
        return $this->belongsTo(LeadSource::class);
    }
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

    
}
