<?php

namespace App\Filament\Resources\PassengerResource\Pages;

use App\Filament\Resources\PassengerResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePassenger extends CreateRecord
{
    protected static string $resource = PassengerResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }
}
