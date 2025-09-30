<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CurrencyResource\Pages;
use App\Models\Currency;
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

class CurrencyResource extends Resource
{
    protected static ?string $model = Currency::class;

    protected static ?string $slug = 'currencies';

    protected static ?string $navigationLabel = "العملات";
    protected static ?string $pluralModelLabel = "العملات";
    protected static ?string $modelLabel = 'عملة';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::CurrencyDollar;

    public static function getNavigationLabel(): string
    {
        return __('dashboard.sidebar.currencies');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([

                TextInput::make('name.ar')
                    ->label(__('dashboard.fields.name_ar'))->suffix("ar")
                    ->required(),

                TextInput::make('name.en')
                    ->label(__('dashboard.fields.name_en'))->suffix("en")
                    ->required(),




                TextInput::make('symbol')
                    ->label(__('dashboard.fields.symbol'))
                    ->required(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label(__('dashboard.fields.name')),

                TextColumn::make('symbol')
                    ->searchable()
                    ->sortable()
                    ->label(__('dashboard.fields.symbol')),
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
            'index' => Pages\ListCurrencies::route('/'),
//            'create' => Pages\CreateCurrency::route('/create'),
//            'edit' => Pages\EditCurrency::route('/{record}/edit'),
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
        return ['name'];
    }
}
