<?php

namespace App\Filament\Resources\AirportRouteResource\Pages;

use App\Filament\Resources\AirportRouteResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAirportRoute extends CreateRecord
{
    protected static string $resource = AirportRouteResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }
}
