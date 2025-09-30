<?php

namespace App\Filament\Resources\TicketSegmentResource\Pages;

use App\Filament\Resources\TicketSegmentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTicketSegment extends CreateRecord
{
    protected static string $resource = TicketSegmentResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }
}
