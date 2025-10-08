<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SafesBank extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'account_number',
        'balance',
        'notes',
    ];

    // علاقة مع الحجوزات
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    // علاقة مع عناصر الحجوزات
    public function reservationItems(): HasMany
    {
        return $this->hasMany(ReservationItem::class);
    }
}
