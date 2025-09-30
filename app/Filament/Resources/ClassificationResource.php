<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClassificationResource\Pages;
use App\Models\Classification;
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
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ClassificationResource extends Resource
{
    protected static ?string $model = Classification::class;

    protected static ?string $slug = 'classifications';

    protected static ?string $navigationLabel = "التصنيفات";
    protected static ?string $pluralModelLabel = "التصنيفات";
    protected static ?string $modelLabel = 'تصنيف';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Tag;

    public static function getNavigationLabel(): string
    {
        return __('dashboard.sidebar.classifications');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('dashboard.fields.classification_info'))
                    ->columns(2) // divide into 2 columns
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('name.ar')
                            ->label(__('dashboard.fields.name_ar'))->suffix("ar")
                            ->required(),

                        TextInput::make('name.en')
                            ->label(__('dashboard.fields.name_en'))->suffix("en")
                            ->required(),
                            TextInput::make('type')
                            ->label(__('dashboard.fields.type')),
        
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

                TextColumn::make('type')
                    ->label(__('dashboard.fields.type')),
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
            'index' => Pages\ListClassifications::route('/'),
            'create' => Pages\CreateClassification::route('/create'),
            'edit' => Pages\EditClassification::route('/{record}/edit'),
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
