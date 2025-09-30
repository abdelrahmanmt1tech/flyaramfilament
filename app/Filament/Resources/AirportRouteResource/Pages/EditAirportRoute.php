<?php

namespace App\Filament\Resources\AirportRouteResource\Pages;

use App\Filament\Resources\AirportRouteResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditAirportRoute extends EditRecord
{
    protected static string $resource = AirportRouteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
