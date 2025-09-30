<?php

namespace App\Filament\Resources\TicketSegmentResource\Pages;

use App\Filament\Resources\TicketSegmentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTicketSegments extends ListRecords
{
    protected static string $resource = TicketSegmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
