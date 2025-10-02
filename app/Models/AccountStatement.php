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
        'passengers',
        'sector',
        'debit',
        'credit',
        'balance',
    ];

    protected $casts = [
        'date' => 'date',
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
        'balance' => 'decimal:2',
        'passengers' => 'array', // important

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

    public static function logTicket($record, $type, $id, $isSupplier = false)
    {
        return self::create([
            'statementable_type' => $type,
            'statementable_id'   => $id,
            'date'               => now(),
            'doc_no'             => $record->ticket_number_core,
            'ticket_id'          => $record->id,
            'passengers'          => $record->passengers()->pluck('first_name')->implode(', '),
            // 'sector'             => $record->itinerary_string,
            'debit'              => $isSupplier ? 0 : $record->sale_total_amount,
            'credit'             => $isSupplier ? $record->sale_total_amount : 0,
            'balance'            => $record->sale_total_amount,
        ]);
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }
}
