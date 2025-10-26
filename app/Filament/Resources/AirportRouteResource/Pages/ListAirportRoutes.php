<?php

namespace App\Filament\Resources\AirportRouteResource\Pages;

use App\Filament\Resources\AirportRouteResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAirportRoutes extends ListRecords
{
    protected static string $resource = AirportRouteResource::class;

    protected function getHeaderActions(): array
    {
        return [
//            CreateAction::make(),
        ];
    }
}
