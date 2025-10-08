<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReservationItem extends Model
{
    // use SoftDeletes;

    protected $fillable = [
        'supplier_id',
        'safes_bank_id',
        'currency_id',
        'reservation_type',
        'date',
        'agent_name',
        'branch',
        'destination',
        'special_requests',
        'additions',
        'notes',
        'added_value',
        'sale_amount',
        'purchase_amount',
        'commission_amount',
        'cash_payment',
        'visa_payment',
        'account_number',
        'from_date',
        'to_date',
        'hotel_name',
        'confirmation_number',
        'room_type',
        'destination_type',
        'room_count',
        'arrival_date',
        'nights_count',
        'departure_date',
        'room_price',
        'total_amount',
        'service_type',
        'document',
        'service_details',
        'additional_info',
        'count',
        'unit_price',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'from_date' => 'date',
            'to_date' => 'date',
            'arrival_date' => 'date',
            'departure_date' => 'date',
            'sale_amount' => 'decimal:2',
            'purchase_amount' => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'cash_payment' => 'decimal:2',
            'visa_payment' => 'decimal:2',
            'room_price' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'room_count' => 'integer',
            'nights_count' => 'integer',
            'count' => 'integer',
        ];
    }

    /**
     * Get the supplier associated with this reservation item
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the safes bank associated with this reservation item
     */
    public function safesBank(): BelongsTo
    {
        return $this->belongsTo(SafesBank::class);
    }

    /**
     * Get the currency associated with this reservation item
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Get the reservation that this item belongs to
     */
    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    /**
     * Check if this is a hotel reservation
     */
    public function isHotel(): bool
    {
        return $this->reservation_type === 'hotel';
    }

    /**
     * Check if this is a car reservation
     */
    public function isCar(): bool
    {
        return $this->reservation_type === 'car';
    }

    /**
     * Check if this is a tourism reservation
     */
    public function isTourism(): bool
    {
        return $this->reservation_type === 'tourism';
    }
}
