<?php

namespace App\Filament\Resources\TicketPassengerResource\Pages;

use App\Filament\Resources\TicketPassengerResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditTicketPassenger extends EditRecord
{
    protected static string $resource = TicketPassengerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
