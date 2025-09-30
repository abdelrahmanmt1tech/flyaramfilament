<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Passenger extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'first_name',
        'last_name',
        'email',
        'phone',

    ];




    public function tickets()
    {
        return $this->belongsToMany(Ticket::class, 'ticket_passengers')
            ->withPivot(['ticket_number_full','ticket_number_prefix','ticket_number_core'])
            ->withTimestamps();
    }



}
