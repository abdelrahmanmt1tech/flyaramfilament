<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TicketPassenger extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'ticket_id',
        'passenger_id',
        'ticket_number_full',
        'ticket_number_prefix',
        'ticket_number_core',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function passenger()
    {
        return $this->belongsTo(Passenger::class);
    }
}
