<?php

namespace App\Filament\Resources\PassengerResource\Pages;

use App\Filament\Resources\PassengerResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditPassenger extends EditRecord
{
    protected static string $resource = PassengerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
