<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TicketPassengerResource\Pages;
use App\Models\TicketPassenger;
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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TicketPassengerResource extends Resource
{
    protected static ?string $model = TicketPassenger::class;
    protected static ?string $slug = 'ticket-passengers';

    protected static ?string $navigationLabel = "مسافرو التذاكر";
    protected static ?string $pluralModelLabel = "مسافرو التذاكر";
    protected static ?string $modelLabel = 'مسافر تذكرة';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::UserCircle;

    public static function getNavigationLabel(): string
    {
        return __('dashboard.sidebar.ticket_passengers');
    }


    public static function canCreate(): bool
    {
        return false;
    }
    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canForceDelete(Model $record): bool
    {
        return false;
    }


    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('ticket.ticket_number')
                    ->label(__('dashboard.fields.ticket_number')),

                TextEntry::make('passenger.first_name')
                    ->label(__('dashboard.fields.first_name')),

                TextEntry::make('passenger.last_name')
                    ->label(__('dashboard.fields.last_name')),

                TextEntry::make('passenger.email')
                    ->label(__('dashboard.fields.email')),

                TextEntry::make('passenger.phone')
                    ->label(__('dashboard.fields.phone')),

                TextEntry::make('ticket_number_full')
                    ->label(__('dashboard.fields.ticket_number_full')),

                TextEntry::make('ticket_number_prefix')
                    ->label(__('dashboard.fields.ticket_number_prefix')),

                TextEntry::make('ticket_number_core')
                    ->label(__('dashboard.fields.ticket_number_core')),

                TextEntry::make('created_at')
                    ->label(__('dashboard.fields.created_date'))
                    ->state(fn(?TicketPassenger $record): string => $record?->created_at?->diffForHumans() ?? '-'),

                TextEntry::make('updated_at')
                    ->label(__('dashboard.fields.last_modified_date'))
                    ->state(fn(?TicketPassenger $record): string => $record?->updated_at?->diffForHumans() ?? '-'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('ticket.ticket_number_full')
                    ->label(__('dashboard.fields.ticket_number'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('passenger.first_name')
                    ->label(__('dashboard.fields.first_name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('passenger.last_name')
                    ->label(__('dashboard.fields.last_name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('passenger.email')
                    ->label(__('dashboard.fields.email'))
                    ->searchable(),

                TextColumn::make('passenger.phone')
                    ->label(__('dashboard.fields.phone'))
                    ->searchable(),

                TextColumn::make('ticket_number_full')
                    ->label(__('dashboard.fields.ticket_number_full'))
                    ->searchable(),

                TextColumn::make('ticket_number_prefix')
                    ->label(__('dashboard.fields.ticket_number_prefix'))
                    ->searchable(),

                TextColumn::make('ticket_number_core')
                    ->label(__('dashboard.fields.ticket_number_core'))
                    ->searchable(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
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
            'index' => Pages\ListTicketPassengers::route('/'),
            // 'create' => Pages\CreateTicketPassenger::route('/create'),
            // 'edit' => Pages\EditTicketPassenger::route('/{record}/edit'),
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
