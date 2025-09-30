<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use SoftDeletes;


    protected $fillable = [
        'gds', 'airline_name', 'validating_carrier_code',
        'ticket_number_full', 'ticket_number_prefix', 'ticket_number_core', 'pnr',
        'issue_date', 'booking_date',
        'ticket_type', 'ticket_type_code', 'trip_type', 'is_domestic_flight',
        'itinerary_string', 'fare_basis_out', 'fare_basis_in',
        'branch_code', 'office_id', 'created_by_user',
        'airline_id', 'currency_id', 'supplier_id', 'sales_user_id', 'client_id', 'branch_id',
        'cost_base_amount', 'cost_tax_amount', 'cost_total_amount',
        'profit_amount', 'discount_amount', 'extra_tax_amount', 'sale_total_amount',
        'carrier_pnr_carrier', 'carrier_pnr', 'price_taxes_breakdown',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'booking_date' => 'date',
        'is_domestic_flight' => 'boolean',
        'price_taxes_breakdown' => 'array',
    ];

    // علاقات
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function airline()
    {
        return $this->belongsTo(Airline::class);
    }         // validating

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function salesAgent()
    {
        return $this->belongsTo(User::class, 'sales_user_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function segments()
    {
        return $this->hasMany(TicketSegment::class);
    }


    public function taxes(): HasMany
    {
        return $this->hasMany(TicketTax::class, 'ticket_id');
    }


    // منطق التسعير: افتراضيًا البيع = التكلفة الشاملة
    protected static function booted()
    {
        static::saving(function (Ticket $t) {
            if (is_null($t->sale_total_amount)) {
                $t->sale_total_amount = ($t->cost_total_amount ?? 0)
                    + ($t->extra_tax_amount ?? 0)
                    + ($t->profit_amount ?? 0)
                    - ($t->discount_amount ?? 0);
            }
        });
    }


    public function passengers()
    {
        return $this->belongsToMany(Passenger::class, 'ticket_passengers')
            ->withPivot(['ticket_number_full', 'ticket_number_prefix', 'ticket_number_core'])
            ->withTimestamps();
    }


}
