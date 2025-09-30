<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
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

}
