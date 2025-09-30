<?php

namespace App\Filament\Resources\TaxTypeResource\Pages;

use App\Filament\Resources\TaxTypeResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditTaxType extends EditRecord
{
    protected static string $resource = TaxTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
