<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AirportRouteResource\Pages;
use App\Models\AirportRoute;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AirportRouteResource extends Resource
{

    protected static string | \UnitEnum | null $navigationGroup = "قوائم تشغيليه" ;



    protected static ?string $model = AirportRoute::class;

    protected static ?string $slug = 'airport-routes';

    protected static ?int $navigationSort = 55;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ArrowsRightLeft;

    public static function getNavigationLabel(): string
    {
        return __('dashboard.sidebar.airport_routes');
    }

//    public static function canAccess(): bool
//    {
//        return false;
//    }

public static function canCreate(): bool
{
    return false;
}


    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('origin_airport_id')
                    ->required(),

                TextEntry::make('created_at')
                    ->label('Created Date')
                    ->state(fn(?AirportRoute $record): string => $record?->created_at?->diffForHumans() ?? '-'),

                TextEntry::make('updated_at')
                    ->label('Last Modified Date')
                    ->state(fn(?AirportRoute $record): string => $record?->updated_at?->diffForHumans() ?? '-'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('origin.name'),
                TextColumn::make('destination.name'),
                TextColumn::make('display_name'),

            ])
            ->filters([
                TrashedFilter::make(),
            ])

                /*
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
            ])
            */
            ;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAirportRoutes::route('/'),
//            'create' => Pages\CreateAirportRoute::route('/create'),
//            'edit' => Pages\EditAirportRoute::route('/{record}/edit'),
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
