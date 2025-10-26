<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use SoftDeletes;


    protected $fillable = [
        'gds',
        'airline_name',
        'validating_carrier_code',
        'ticket_number_full',
        'ticket_number_prefix',
        'ticket_number_core',
        'pnr',
        'issue_date',
        'booking_date',



        'ticket_type',
        'ticket_type_code',
        'trip_type',
        'is_domestic_flight',
        'itinerary_string',
        'fare_basis_out',
        'fare_basis_in',


        'branch_code',


        'office_id',



        'pnr_branch_code',
        'pnr_office_id',
        'issuing_office_id',
        'issuing_carrier',
        'created_by_user',

        'carrier_pnr_carrier',
        'carrier_pnr',

        'cost_base_amount',
        'cost_tax_amount',
        'cost_total_amount',


        'profit_amount',
        'discount_amount',
        'extra_tax_amount',
        'sale_total_amount',


        'airline_id',
        'currency_id',
        'supplier_id',
        'sales_user_id',
        'client_id',
        'branch_id',
        'franchise_id',


        'price_taxes_breakdown',



        // NEW

        'tax_type_id',
        'is_invoiced',
        "sales_rep" ,
        "is_purchased" ,
        "is_refunded" ,


    ];

    protected $casts = [
        'issue_date' => 'date',
        'booking_date' => 'date',
        'is_domestic_flight' => 'boolean',
        'price_taxes_breakdown' => 'array',
    ];


    public function accountStatement()
    {
        return $this->hasMany(AccountStatement::class);
    }

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





    public function passengers()
    {
        return $this->belongsToMany(Passenger::class, 'ticket_passengers')
            ->withPivot(['ticket_number_full', 'ticket_number_prefix', 'ticket_number_core'])
            ->withTimestamps();
    }

    public function franchise()
    {
        return $this->belongsTo(Franchise::class);
    }

    public function taxType()
    {
        return $this->belongsTo(TaxType::class);
    }

    public function accountStatements()
    {
        return $this->morphMany(AccountStatement::class, 'statementable');
    }


    public function createAccountTax()
    {
        if ($this->is_domestic_flight) {

            $internal_tax_percentage = TaxType::where('id', 1)->value('value') ?? 15;

            $internal_tax_value = $this->cost_total_amount * ($internal_tax_percentage / 100);

            AccountTax::updateOrCreate(
                ['ticket_id' => $this->id, 'type' => 'purchase_tax'],
                [
                    'tax_percentage' => $internal_tax_percentage,
                    'tax_value'      => $internal_tax_value,
                    'tax_types_id'    => 1,
                    'is_returned'    => false,
                ]
            );


           $sale_tax_percentage = TaxType::where('id', 2)->value('value') ?? 15;

            $sale_tax_value = max($this->sale_total_amount, $this->cost_total_amount)
            * ($sale_tax_percentage / 100);


            AccountTax::updateOrCreate(
                ['ticket_id' => $this->id, 'type' => 'sales_tax'],
                [
                    'tax_percentage' => $sale_tax_percentage,
                    'tax_value'      => $sale_tax_value,
                    'tax_types_id'    => 2,
                    'is_returned'    => false,
                ]
            );
        }else{



              AccountTax::updateOrCreate(
                ['ticket_id' => $this->id, 'type' => 'sales_tax'],
                [
                    'tax_percentage' => $this->taxType()->value('value') ?? 0,
                    'tax_value'      => $this->extra_tax_amount ?? 0,
                    'tax_types_id'   => $this->tax_type_id,
                    'is_returned'    => false,
                ]
            );



        }
    }


    protected static function booted()
    {


        static::saving(function (Ticket $t) {

            $price = ($t->cost_total_amount ?? 0);
            if ($t->is_domestic_flight){
                $price +=  ($t->extra_tax_amount ?? 0) ;
            }
            $t->sale_total_amount = $price
                + ($t->profit_amount ?? 0)
                - ($t->discount_amount ?? 0);
        });



        static::saved(function (Ticket $t) {
            $t->createAccountTax();
        });
    }


    public function invoices()
    {
        return $this->belongsToMany(Invoice::class, 'invoice_ticket');
    }



}
