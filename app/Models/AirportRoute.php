<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AirportRoute extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'origin_airport_id', 'destination_airport_id', 'display_name'
    ];

    public function origin(){ return $this->belongsTo(Airport::class, 'origin_airport_id'); }
    public function destination(){ return $this->belongsTo(Airport::class, 'destination_airport_id'); }

}
