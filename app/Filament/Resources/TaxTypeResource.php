<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaxTypeResource\Pages;
use App\Models\TaxType;
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

class TaxTypeResource extends Resource
{
    protected static ?string $model = TaxType::class;

    protected static ?string $slug = 'tax-types';

    protected static ?string $navigationLabel = "أنواع الضرائب";
    protected static ?string $pluralModelLabel = "أنواع الضرائب";
    protected static ?string $modelLabel = 'نوع ضريبة';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ReceiptPercent;

    public static function getNavigationLabel(): string
    {
        return __('dashboard.sidebar.tax_types');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->label(__('dashboard.fields.name')),

                TextInput::make('value')
                    ->required()
                    ->numeric()
                    ->label(__('dashboard.fields.value')),

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

                TextColumn::make('value')
                    ->label(__('dashboard.fields.value')),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn ($record) => $record->id !== 1),
            
                DeleteAction::make()
                    ->visible(fn ($record) => $record->id !== 1),
            
                // RestoreAction::make()
                //     ->visible(fn ($record) => $record->id !== 1),
            
                // ForceDeleteAction::make()
                //     ->visible(fn ($record) => $record->id !== 1),
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
            'index' => Pages\ListTaxTypes::route('/'),
            // 'create' => Pages\CreateTaxType::route('/create'),
            // 'edit' => Pages\EditTaxType::route('/{record}/edit'),
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
