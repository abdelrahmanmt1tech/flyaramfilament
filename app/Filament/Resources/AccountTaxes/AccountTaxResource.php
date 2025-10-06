<?php

namespace App\Filament\Resources\AccountTaxes;

use App\Filament\Resources\AccountTaxes\Pages\ManageAccountTaxes;
use App\Models\AccountTax;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AccountTaxResource extends Resource
{
    protected static ?string $model = AccountTax::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Ticket;

    protected static ?string $navigationLabel = "سجل الضرائب";
    protected static ?string $pluralModelLabel = "سجل الضرائب";
    protected static ?string $modelLabel = 'سجل الضرائب';





    public static function canCreate(): bool
    {
        return false;
    }

    protected static ?string $recordTitleAttribute = 'tax_value';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('ticket_id')
                    ->relationship('ticket', 'id')
                    ->required(),

                TextInput::make('type')
                    ->required(),
                TextInput::make('tax_percentage')
                    ->numeric(),
                TextInput::make('tax_value')
                    ->numeric(),
                TextInput::make('tax_types_id')
                    ->numeric(),
                Toggle::make('is_returned')
                    ->required(),
                TextInput::make('zakah_id'),
                TextInput::make('zakah_response'),
                TextInput::make('zakah_status'),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
          /*      TextEntry::make('ticket.ticket_number_core'),

                TextEntry::make('type')
                ->state(fn($state)=>match ($state)
                {
                        'sales_tax'=>"ضريبه مبيعات" ,
                        'purchase_tax'=>"مشتريات داخلي " ,
                }

                )
                ,*/
                TextEntry::make('tax_percentage')
                    ->numeric(),
                TextEntry::make('tax_value')
                    ->numeric(),

                TextEntry::make('taxType.name'),

                IconEntry::make('is_returned') ->boolean(),

                TextEntry::make('zakah_id'),
                TextEntry::make('zakah_status'),
                TextEntry::make('deleted_at')
                    ->dateTime(),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('tax_value')
            ->defaultCurrency("SAR")
            ->columns([

                TextColumn::make('ticket.ticket_number_core')->copyable()
                    ->sortable()
                    ->searchable() ,

                TextColumn::make('taxType.name'),
                TextColumn::make('type')
                    ->formatStateUsing(fn($state)=>match ($state)
                    {
                        'sales_tax'=>"ضريبه مبيعات" ,
                        'purchase_tax'=>" ضريبه مشتريات داخلي " ,
                    }

                    )->badge()
                    ,



                TextColumn::make('tax_percentage')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('tax_value')
                    ->numeric()
                    ->sortable()
                    ->summarize([
                        Sum::make()
                            ->label('إجمالي')
                            ->numeric()
                    ]),


                TextColumn::make('ticket.cost_total_amount')->label("cost") ,
                TextColumn::make('ticket.profit_amount') ->label("profit"),
                TextColumn::make('ticket.sale_total_amount')->label("sale") ,





//                IconColumn::make('is_returned')
//                    ->boolean(),
//                TextColumn::make('zakah_id')
//                    ->searchable(),
//                TextColumn::make('zakah_status')
//                    ->searchable(),



                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
//                EditAction::make(),
//                DeleteAction::make(),
//                ForceDeleteAction::make(),
//                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
//                    DeleteBulkAction::make(),
//                    ForceDeleteBulkAction::make(),
//                    RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageAccountTaxes::route('/'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
