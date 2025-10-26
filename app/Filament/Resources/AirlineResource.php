<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AirlineResource\Pages;
use App\Models\Airline;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class AirlineResource extends Resource
{
    protected static ?string $model = Airline::class;

    protected static ?string $slug = 'airlines';

    protected static ?int $navigationSort = 60;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::BuildingOffice;
    protected static ?string $navigationLabel = "الشركات";
    protected static ?string $pluralModelLabel = "الشركات";
    protected static ?string $modelLabel = 'شركة';


    protected static string | \UnitEnum | null $navigationGroup = "قوائم تشغيليه" ;



    public static function canViewAny(): bool
    {
        return Auth::user()->can('airlines.view');
    }

    public static function canCreate(): bool
    {
        return Auth::user()->can('airlines.create');
    }

    public static function canEdit($record): bool
    {
        return Auth::user()->can('airlines.update');
    }

    public static function canDelete($record): bool
    {
        return Auth::user()->can('airlines.delete');
    }

    public static function getNavigationLabel(): string
    {
        return __('dashboard.sidebar.airlines');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('dashboard.fields.airline_info')) // Section title
                    ->columns(2) // divide into 2 columns
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('name.ar')
                            ->label(__('dashboard.fields.name_ar'))
                            ->suffix("ar")
                            ->required(),

                        TextInput::make('name.en')
                            ->label(__('dashboard.fields.name_en'))
                            ->suffix("en")
                            ->required(),

                        TextInput::make('iata_code')
                            ->label(__('dashboard.fields.iata_code'))
                            ->placeholder('مثال: SV للسعودية')
                            ->maxLength(2),

                        TextInput::make('iata_prefix')
                            ->label(__('dashboard.fields.iata_prefix'))
                            ->placeholder('مثال: 096')
                            ->maxLength(3)
                            ->numeric(),

                        TextInput::make('icao_code')
                            ->label(__('dashboard.fields.icao_code'))
                            ->placeholder('مثال: SVA للسعودية')
                            ->maxLength(3),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('dashboard.fields.name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('iata_code')
                    ->label(__('dashboard.fields.iata_code'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('iata_prefix')
                    ->label(__('dashboard.fields.iata_prefix')),

                TextColumn::make('icao_code')
                    ->label(__('dashboard.fields.icao_code')),

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
            'index' => Pages\ListAirlines::route('/'),
            'create' => Pages\CreateAirline::route('/create'),
            'edit' => Pages\EditAirline::route('/{record}/edit'),
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
        return ['name', 'iata_code', 'icao_code'];
    }
}
