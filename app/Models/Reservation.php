<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reservation extends Model
{
    // use SoftDeletes;

    protected $fillable = [
        'related_id',
        'related_type',
        'ticket_id',
        'passenger_id',
        'reservation_number',
    ];

    /**
     * Get the related model (Client, Supplier, Branch, or Franchise)
     */
    public function related(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the ticket associated with this reservation
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * Get the passenger associated with this reservation
     */
    public function passenger(): BelongsTo
    {
        return $this->belongsTo(Passenger::class);
    }

    /**
     * Get the reservation items for this reservation
     */
    public function items()
    {
        return $this->hasMany(ReservationItem::class);
    }
}
