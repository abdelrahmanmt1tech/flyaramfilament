<?php

namespace App\Filament\Resources\TicketPassengerResource\Pages;

use App\Filament\Resources\TicketPassengerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTicketPassengers extends ListRecords
{
    protected static string $resource = TicketPassengerResource::class;

    protected function getHeaderActions(): array
    {
        return [
//            CreateAction::make(),
        ];
    }
}
