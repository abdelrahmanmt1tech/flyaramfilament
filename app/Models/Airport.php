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
        'is_internal',
        'country_code',
    ];


    protected function casts(): array
    {
        return [
            'is_internal' => 'boolean',
        ];
    }



}
