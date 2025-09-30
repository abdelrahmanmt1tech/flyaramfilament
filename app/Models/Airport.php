<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Airport extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'iata',
        'name',
        'city',
        'country_code',
    ];
}
