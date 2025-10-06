<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Filament\SharedForms\ContactInfoForm;
use App\Models\Client;
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
use Filament\Forms\Components\Select;
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
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static ?string $slug = 'clients';

    protected static ?string $navigationLabel = "العملاء";
    protected static ?string $pluralModelLabel = "العملاء";
    protected static ?string $modelLabel = 'عميل';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::UserGroup;

    public static function getNavigationLabel(): string
    {
        return __('dashboard.sidebar.clients');
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
                                    ->label(__('dashboard.fields.name_ar'))->suffix("ar")
                                    ->required(),

                                TextInput::make('name.en')
                                    ->label(__('dashboard.fields.name_en'))->suffix("en")
                                    ->required(),

                                TextInput::make('company_name.ar')
                                    ->label(__('dashboard.fields.company_name_ar'))->suffix("ar")
                                    ->required(),

                                TextInput::make('company_name.en')
                                    ->label(__('dashboard.fields.company_name_en'))->suffix("en")
                                    ->required(),
                            ]),

                        TextInput::make('tax_number')
                            ->label(__('dashboard.fields.tax_number'))
                            ->required()
                            ->columnSpanFull(),

                        Select::make('sales_rep_id')
                            ->relationship('salesRep', 'name')
                            ->label(__('dashboard.fields.sales_rep'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpanFull(),

                        Select::make('lead_source_id')
                            ->relationship('leadSource', 'name')
                            ->label(__('dashboard.fields.lead_source'))
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                TextInput::make('name.ar')
                                    ->label(__('dashboard.fields.name_ar'))->suffix("ar")
                                    ->required(),

                                TextInput::make('name.en')
                                    ->label(__('dashboard.fields.name_en'))->suffix("en")
                                    ->required()
                            ])
                            ->required()
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

                TextColumn::make('company_name')
                    ->label(__('dashboard.fields.company_name')),

                TextColumn::make('tax_number')
                    ->label(__('dashboard.fields.tax_number')),
                TextColumn::make('contactInfos.phone')
                    ->label(__('dashboard.fields.phone'))
                    ->getStateUsing(fn ($record) => $record->contactInfos()->first()?->phone ?? '-')
                    ->sortable(false)
                    ->searchable(false),

                TextColumn::make('salesRep.name')
                    ->label(__('dashboard.fields.sales_rep'))
                    ->searchable()
                    ->sortable(),
                    TextColumn::make('leadSource.name')
                    ->label(__('dashboard.fields.lead_source'))
                    ->sortable()
                    ->searchable(),
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
            'index' => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['salesRep']);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'salesRep.name'];
    }







    public static function getGlobalSearchResultDetails(Model $record): array
    {
        $details = [];
        if ($record->salesRep) {
            $details['SalesRep'] = $record->salesRep->name;
        }
        return $details;
    }
}
