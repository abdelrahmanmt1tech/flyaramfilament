<?php

namespace App\Filament\Resources\FreeInvoices\Pages;

use App\Filament\Resources\FreeInvoices\FreeInvoiceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFreeInvoice extends EditRecord
{
    protected static string $resource = FreeInvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
