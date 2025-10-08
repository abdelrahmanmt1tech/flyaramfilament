<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Currency extends Model
{
    use SoftDeletes;

    use HasTranslations;
    public array $translatable = ['name'];



    protected $fillable = [
        'name',
        'symbol',
    ];

    // علاقة مع عناصر الحجوزات
    public function reservationItems()
    {
        return $this->hasMany(ReservationItem::class);
    }
}
