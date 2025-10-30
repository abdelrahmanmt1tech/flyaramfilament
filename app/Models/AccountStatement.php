<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\DB;

class AccountStatement extends Model
{

    protected $fillable = [
        'statementable_type',
        'statementable_id',
        'date',
        'doc_no',
        'ticket_id',
        'lpo_no',
        // 'passengers',
        'sector',
        'debit',
        'credit',
        'balance',
        'reservation_id',
        'type'
    ];

    protected $casts = [
        'date' => 'date',
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
        'balance' => 'decimal:2',

    ];


    public function client(): BelongsTo
    {
        return $this->belongsTo(client::class, 'statementable_id')->where('statementable_type', Client::class);
    }

    /**
     * Get the parent statementable model (polymorphic relationship).
     */
    public function statementable(): MorphTo
    {
        return $this->morphTo();
    }

    protected static function booted()
    {
        static::creating(function ($statement) {

            $previousBalance = self::where('statementable_type', $statement->statementable_type)
                ->where('statementable_id', $statement->statementable_id)
                ->sum(DB::raw('credit - debit'));


            $statement->balance = $previousBalance + ($statement->credit - $statement->debit);
        });
    }

    public static function logTicket($record, $type, $id, $isSupplier = false , $statemenType = 'sale')
    {
        return self::create([
            'statementable_type' => $type,
            'statementable_id'   => $id,
            'date'               => now(),
            'doc_no'             => $record->ticket_number_core,
            'ticket_id'          => $record->id,
            // 'passengers'          => $record->passengers()->pluck('first_name')->implode(', '),
            // 'sector'             => $record->itinerary_string,
            'debit'              => $isSupplier ? 0 : $record->sale_total_amount,
            'credit'             => $isSupplier ? $record->sale_total_amount : 0,
            'balance'            => $record->sale_total_amount,
            'type'            => $statemenType,

        ]);
    }

    /**
     * Log an account statement entry for a reservation.
     * Debit = sum of reservation items' total_amount, Credit = 0
     */
    public static function logReservation(Reservation $reservation , $type = 'sale')
    {
        $total = (float) $reservation->total_with_tax;

        return self::create([
            'statementable_type' => $reservation->related_type,
            'statementable_id'   => $reservation->related_id,
            'ticket_id'          => $reservation->ticket_id,
            'date'               => now(),
            'doc_no'             => $reservation->reservation_number,
            'debit'              => $total,
            'credit'             => 0,
            'reservation_id'     => $reservation->id,
            'type' => $type,

        ]);
    }




    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }

    public function reservation()
    {
        return $this->belongsTo(Reservation::class, 'reservation_id');
    }

    public function passengers()
    {
        return $this->hasManyThrough(Passenger::class, TicketPassenger::class, 'ticket_id', 'id', 'ticket_id', 'passenger_id');
    }

    // public function invoices()
    // {
    //     return $this->hasManyThrough(
    //         Invoice::class,
    //         Ticket::class,
    //         'id', // المفتاح في tickets
    //         'id', // المفتاح في invoices
    //         'ticket_id', // المفتاح في account_statements
    //         'id' // المفتاح في tickets
    //     );
    // }

    public function invoices()
    {
        return $this->belongsToMany(
            Invoice::class,
            'invoice_ticket',
            'ticket_id',  // المفتاح في جدول pivot
            'invoice_id', // المفتاح في جدول pivot
            'ticket_id',  // المفتاح في account_statements
            'id'          // المفتاح في tickets
        );
    }

    public function reservationInvoices()
{
    return $this->hasManyThrough(
        Invoice::class,
        Reservation::class,
        'id',            // المفتاح في reservations
        'reservation_id', // المفتاح في invoices
        'reservation_id', // المفتاح في account_statements
        'id'              // المفتاح في reservations
    );
}



    public function refundInvoice()
    {
        return $this->invoices()->where('type', 'refund');
    }


    public function saleInvoice()
    {
        return $this->invoices()->where('type', 'sale');
    }
    public function purchaseInvoice()
    {
        return $this->invoices()->where('type', 'purchase');
    }


    public function hasRefundInvoice()
    {
        return $this->refundInvoice()->exists();
    }


    public function hasOriginalInvoice()
    {
        return $this->saleInvoice()->exists();
    }
}
