<?php

namespace App\Filament\Resources\TicketPassengerResource\Pages;

use App\Filament\Resources\TicketPassengerResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTicketPassenger extends CreateRecord
{
    protected static string $resource = TicketPassengerResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }
}
