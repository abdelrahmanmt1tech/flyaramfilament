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

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name.ar')
                    ->label('Name')->suffix("ar")
                    ->required(),

                TextInput::make('name.en')
                    ->label('Name')->suffix("en")
                    ->required(),


                TextInput::make('company_name.ar')
                    ->label('Company Name')->suffix("ar")
                    ->required(),

                TextInput::make('company_name.en')
                    ->label('Company Name')->suffix("en")
                    ->required(),


                TextInput::make('tax_number')
                    ->required(),

                Select::make('sales_rep_id')
                    ->relationship('salesRep', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),


                Select::make('lead_source_id')
                    ->relationship('leadSource', 'name')
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        TextInput::make('name.ar')
                            ->label('Name')->suffix("ar")
                            ->required(),

                        TextInput::make('name.en')
                            ->label('Name')->suffix("en")
                            ->required()

                    ])
                    ->required(),


//
//                Select::make('classifications')
//                    ->relationship('classifications', 'name')
//                    ->searchable()
//                    ->preload()
//                    ->required(),


                Repeater::make('contactInfos')
                    ->relationship('contactInfos')
                    ->schema(ContactInfoForm::make())
                    ->label('معلومات التواصل')
                    ->reorderable()
                    ->collapsible()
                    ->grid(2),


                TextEntry::make('created_at')
                    ->label('Created Date')
                    ->state(fn(?Client $record): string => $record?->created_at?->diffForHumans() ?? '-'),

                TextEntry::make('updated_at')
                    ->label('Last Modified Date')
                    ->state(fn(?Client $record): string => $record?->updated_at?->diffForHumans() ?? '-'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('company_name'),


                TextColumn::make('tax_number'),

                TextColumn::make('salesRep.name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('lead_source_id'),
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
        return ['name', 'email', 'salesRep.name'];
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
