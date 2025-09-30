<?php

namespace App\Filament\Resources\PassengerResource\Pages;

use App\Filament\Resources\PassengerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPassengers extends ListRecords
{
    protected static string $resource = PassengerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
