<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AirportResource\Pages;
use App\Models\Airport;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section as ComponentsSection;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AirportResource extends Resource
{
    protected static ?string $model = Airport::class;

    protected static ?string $slug = 'airports';

    protected static ?int $navigationSort = 50;

    protected static ?string $navigationLabel = "المطارات";
    protected static ?string $pluralModelLabel = "المطارات";
    protected static ?string $modelLabel = 'مطار';




    protected static string|BackedEnum|null $navigationIcon = Heroicon::MapPin;

    public static function getNavigationLabel(): string
    {
        return __('dashboard.sidebar.airports');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                ComponentsSection::make(__('dashboard.fields.airport_info')) // Section title
                    ->columns(2) // divide into 2 columns
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('iata')
                            ->label(__('dashboard.fields.iata'))
                            ->placeholder('مثال: JED لجدة'),

                        TextInput::make('name')
                    ->label(__('dashboard.fields.name'))
                    ->required(),

                TextInput::make('city')
                    ->label(__('dashboard.fields.city')),

                ToggleColumn::make('is_internal')
                    ->default(false)
                    ->label(__('dashboard.is_internal')),

                TextInput::make('country_code')
                   ->maxLength(5)
                    ->label(__('dashboard.fields.country_code'))
                    ->placeholder('مثال: SA للسعودية'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('iata')
                    ->label(__('dashboard.fields.iata'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label(__('dashboard.fields.name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('city')
                    ->label(__('dashboard.fields.city'))
                    ->searchable(),

                TextColumn::make('country_code')
                    ->label(__('dashboard.fields.country_code')),
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
            'index' => Pages\ListAirports::route('/'),
            'create' => Pages\CreateAirport::route('/create'),
            'edit' => Pages\EditAirport::route('/{record}/edit'),
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
        return ['name', 'iata', 'city'];
    }
}
