<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TicketSegment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'ticket_id','segment_index','origin_airport_id','destination_airport_id','route_id',
        'carrier_airline_id','carrier_code','flight_number','booking_class','status','equipment','baggage','meal',
        'departure_at','arrival_at','origin_country','destination_country','fare_basis',
    ];

    protected $casts = [
        'departure_at' => 'datetime',
        'arrival_at'   => 'datetime',
    ];

    public function ticket(){ return $this->belongsTo(Ticket::class); }
    public function origin(){ return $this->belongsTo(Airport::class, 'origin_airport_id'); }
    public function destination(){ return $this->belongsTo(Airport::class, 'destination_airport_id'); }
    public function route(){ return $this->belongsTo(AirportRoute::class, 'route_id'); }
    public function carrierAirline(){ return $this->belongsTo(Airline::class, 'carrier_airline_id'); }




}
