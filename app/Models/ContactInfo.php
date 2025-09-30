<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactInfo extends Model
{
    protected $fillable = [
        'phone', 'email', 'address', 'whatsapp', 'website'
    ];

    public function contactable()
    {
        return $this->morphTo();
    }


}
