<?php

namespace App\Filament\Resources\TicketSegmentResource\Pages;

use App\Filament\Resources\TicketSegmentResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditTicketSegment extends EditRecord
{
    protected static string $resource = TicketSegmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
