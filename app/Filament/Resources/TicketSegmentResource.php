<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TicketSegmentResource\Pages;
use App\Models\TicketSegment;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class TicketSegmentResource extends Resource
{
    protected static ?string $model = TicketSegment::class;

    protected static ?string $slug = 'ticket-segments';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::QueueList;

    public static function getNavigationLabel(): string
    {
        return __('dashboard.sidebar.ticket_segments');
    }

          public static function canViewAny(): bool
    {
        return Auth::user()->can('ticket_segments.view');
    }

    public static function canCreate(): bool
    {
        return Auth::user()->can('ticket_segments.create');
    }

    public static function canEdit($record): bool
    {
        return Auth::user()->can('ticket_segments.update');
    }

    public static function canDelete($record): bool
    {
        return Auth::user()->can('ticket_segments.delete');
    }

    public static function canAccess(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('created_at')
                    ->label('Created Date')
                    ->state(fn(?TicketSegment $record): string => $record?->created_at?->diffForHumans() ?? '-'),

                TextEntry::make('updated_at')
                    ->label('Last Modified Date')
                    ->state(fn(?TicketSegment $record): string => $record?->updated_at?->diffForHumans() ?? '-'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTicketSegments::route('/'),
            'create' => Pages\CreateTicketSegment::route('/create'),
            'edit' => Pages\EditTicketSegment::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [];
    }
}
