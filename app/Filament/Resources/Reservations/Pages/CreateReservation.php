<?php

namespace App\Filament\Resources\Reservations\Pages;

use App\Filament\Resources\Reservations\ReservationResource;
use App\Models\AccountStatement;
use Illuminate\Support\Collection;
use Filament\Resources\Pages\CreateRecord;

class CreateReservation extends CreateRecord
{
    protected static string $resource = ReservationResource::class;

    protected function afterCreate(): void
    {
        // Try summing from persisted relation first
        $sum = (float) $this->record->total_with_tax;

        if ($sum > 0) {
            // Ensure we have related info available on the record
            $this->record->refresh();
            AccountStatement::logReservation($this->record, 'sale');
        }
    }
}
