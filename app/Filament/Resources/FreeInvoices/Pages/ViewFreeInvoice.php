<?php

namespace App\Filament\Resources\FreeInvoices\Pages;

use App\Filament\Resources\FreeInvoices\FreeInvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewFreeInvoice extends ViewRecord
{
    protected static string $resource = FreeInvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
