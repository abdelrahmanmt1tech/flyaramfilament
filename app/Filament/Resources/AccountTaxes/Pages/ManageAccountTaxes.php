<?php

namespace App\Filament\Resources\AccountTaxes\Pages;

use App\Filament\Resources\AccountTaxes\AccountTaxResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageAccountTaxes extends ManageRecords
{
    protected static string $resource = AccountTaxResource::class;

    protected function getHeaderActions(): array
    {
        return [
//            CreateAction::make(),
        ];
    }
}
