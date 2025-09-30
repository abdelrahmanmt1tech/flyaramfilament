<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BranchResource\Pages;
use App\Filament\SharedForms\ContactInfoForm;
use App\Models\Branch;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BranchResource extends Resource
{
    protected static ?string $model = Branch::class;

    protected static ?string $slug = 'branches';

    protected static ?string $navigationLabel = "الفروع";
    protected static ?string $pluralModelLabel = "الفروع";
    protected static ?string $modelLabel = 'فرع';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::BuildingStorefront;
    
    public static function getNavigationLabel(): string
    {
        return __('dashboard.sidebar.branches');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('dashboard.fields.basic_info'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name.ar')
                                    ->label(__('dashboard.fields.name_ar'))
                                    ->suffix("ar")
                                    ->required(),

                                TextInput::make('name.en')
                                    ->label(__('dashboard.fields.name_en'))
                                    ->suffix("en")
                                    ->required(),
                            ]),

                        TextInput::make('tax_number')
                            ->label(__('dashboard.fields.tax_number'))
                            ->columnSpanFull(),
                    ]),

                Section::make(__('dashboard.fields.contact_info'))
                    ->schema([
                        Repeater::make('contactInfos')
                            ->relationship('contactInfos')
                            ->schema(ContactInfoForm::make())
                            ->label(__('dashboard.fields.contact_infos'))
                            ->reorderable()
                            ->collapsible()
                            ->grid(2)
                            ->defaultItems(0)
                            ->addActionLabel(__('dashboard.fields.add_contact_info')),
                    ])
                    ->collapsible(),
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

                TextColumn::make('tax_number')
                    ->label(__('dashboard.fields.tax_number')),

                TextColumn::make('contactInfos.phone')
                    ->label(__('dashboard.fields.phone'))
                    ->getStateUsing(fn ($record) => $record->contactInfos()->first()?->phone ?? '-')
                    ->sortable(false)
                    ->searchable(false),
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
            'index' => Pages\ListBranches::route('/'),
            'create' => Pages\CreateBranch::route('/create'),
            'edit' => Pages\EditBranch::route('/{record}/edit'),
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