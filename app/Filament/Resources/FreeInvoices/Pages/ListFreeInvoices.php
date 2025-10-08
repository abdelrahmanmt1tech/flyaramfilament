<?php

namespace App\Filament\Resources\FreeInvoices\Pages;

use App\Filament\Resources\FreeInvoices\FreeInvoiceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFreeInvoices extends ListRecords
{
    protected static string $resource = FreeInvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
