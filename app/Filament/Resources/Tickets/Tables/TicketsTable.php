<?php

namespace App\Filament\Resources\Tickets\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TicketsTable
{
    public static function configure(Table $table): Table
    {
        return $table

            ->modifyQueryUsing(function (Builder $query): Builder {
                return $query->with('currency');
            })
            ->columns([

                // Ticket No
                TextColumn::make('ticket_number_core')
                    ->label('Ticket No')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('gds')
                    ->searchable()
                    ->sortable()
                   ,


                // Booking Date
                TextColumn::make('booking_date')
                    ->label('Booking Date')
                    ->state(fn($record) => ($record?->booking_date || $record?->issue_date))
                    ->date()
                    ->sortable(),



                // Passenger (أول اسم / عدد)
                TextColumn::make('passengers')
                    ->label('Passenger')
                    ->state(fn($record) => $record->passengers()->limit(3)
                        ->pluck('first_name')->implode(' | '))
                ,

                TextColumn::make('office_id')
                    ->label('#Branch')
                    ->prefix(fn($record) => $record?->branch_code)
                    ->sortable(),

                TextColumn::make('created_by_user')
                    ->label('#User')
//                    ->prefix(fn($record) => $record?->branch_code)
                    ->sortable(),


                TextColumn::make('ticket_type_code')->label('Type Code')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->badge(),
//
//                TextColumn::make('ticket_type')->label('Type Name')
//                    ->toggleable(isToggledHiddenByDefault: true),


                IconColumn::make('is_domestic_flight')
                    ->label('INTERNAL')
                    ->boolean()
                    ->sortable(),




                TextColumn::make('cost_total_amount')
                    ->label('Cost')
                    ->money(fn($record) => $record->currency?->symbol ?: 'SAR', true)
                    ->sortable(),

                // Price For Sale (النهائي)
                TextColumn::make('sale_total_amount')
                    ->label('Price For Sale')

                    ->badge()
                    ->color(function($record)  {
                        if ($record->sale_total_amount < $record->cost_total_amount ){
                            return "danger";
                        }
                        return "info" ;
                    })


                    ->money(fn($record) => $record->currency?->symbol ?: 'SAR', true)
                    ->sortable(),

                // Profits
                TextColumn::make('profit_amount')
                    ->label('Profits')
                    ->badge()
                    ->state(fn($record)=> $record->profit_amount ?? "-")
                    ->color(function($record)  {
                        if (!$record->profit_amount){
                            return "danger"; ;
                        }
                        if ($record->profit_amount < 10 ){
                            return "warning" ;
                        }
                        return "success" ;
                    })
                    ->money(fn($record) => $record->currency?->symbol ?: 'SAR', true)
                    ->sortable()
                    ->toggleable(),




                TextColumn::make('airline.name')
                    ->label('Airline')
                    ->placeholder(fn($record) => $record->airline_name)
                    ->searchable()
                    ->sortable(),

                // Flights (مسار مختصر)
                TextColumn::make('itinerary_string')
                    ->label('Flights')
                    ->limit(40)
                    ->tooltip(fn($record) => $record->itinerary_string ?? "-")
                    ->sortable(),


                TextColumn::make('trip_type')
                    ->label('TYPE')
                    ->badge()
                    ->colors([
                        'success' => 'ONE-WAY',
                        'warning' => 'ROUND-TRIP',
                        'info' => 'MULTI-SEG',
                    ])
                    ->sortable(),





                // Branch
                /*            TextColumn::make('branch.name')
                                ->label('Branch')
                                ->toggleable(isToggledHiddenByDefault: true)
                                ->sortable()
                                ->searchable(),*/

                // User (مندوب/وكيل بيع)
                /*               TextColumn::make('salesAgent.code')
                                   ->label('User')
                                   ->placeholder(fn($r) => $r->created_by_user)
                                   ->tooltip(fn($r) => $r->salesAgent?->name ?: $r->created_by_user)
                                   ->sortable()
                                   ->searchable(), */

                // TYPE (ONE-WAY / ROUND-TRIP / MULTI-SEG)



                // INTERNAL (رحلة داخلية؟)


                // Cost (شامل الضرائب)


                // VAT (ضرائب النظام الإضافية — من Pivot ticket_tax_types)
                /*          TextColumn::make('vat_total')
                              ->label('VAT')
                              ->state(fn(\App\Models\Ticket $r) => (float) $r->taxTypes()->sum('ticket_tax_types.amount'))
                              ->money(fn($r) => optional($r->currency)->symbol ?: 'SAR', true)
                              ->sortable(),*/

                // Airline (validating)


                // ======== باقي الحقول (togglable) ========

                /*

                TextColumn::make('pnr')->label('PNR')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->copyable()
                    ->searchable(),

                TextColumn::make('validating_carrier_code')->label('Validating')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->badge(),

                TextColumn::make('branch_code')->label('Branch Code')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('office_id')->label('Office ID')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_by_user')->label('Created By')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('fare_basis_out')->label('Fare Basis Out')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('fare_basis_in')->label('Fare Basis In')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('issue_date')->label('Issue Date')
                    ->date('Y-m-d')->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('cost_base_amount')->label('Cost (Base)')
                    //->money(fn($r) => optional($r->currency)->symbol ?: 'SAR', true)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('cost_tax_amount')->label('Cost (Taxes)')
                    //->money(fn($r) => optional($r->currency)->symbol ?: 'SAR', true)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('discount_amount')->label('Discount')
                    //->money(fn($r) => optional($r->currency)->symbol ?: 'SAR', true)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('extra_tax_amount')->label('Extra Tax')
                    //->money(fn($r) => optional($r->currency)->symbol ?: 'SAR', true)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('carrier_pnr')->label('Carrier PNR')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->copyable(),




                */



            ])

                /*
            ->columns([



                TextColumn::make('gds')
                    ->searchable(),
                TextColumn::make('airline_name')
                    ->searchable(),
                TextColumn::make('validating_carrier_code')
                    ->searchable(),
                TextColumn::make('ticket_number_full')
                    ->searchable(),
                TextColumn::make('ticket_number_prefix')
                    ->searchable(),
                TextColumn::make('ticket_number_core')
                    ->searchable(),
                TextColumn::make('pnr')
                    ->searchable(),
                TextColumn::make('issue_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('booking_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('ticket_type')
                    ->searchable(),
                TextColumn::make('ticket_type_code')
                    ->searchable(),
                TextColumn::make('trip_type')
                    ->searchable(),
                IconColumn::make('is_domestic_flight')
                    ->boolean(),
                TextColumn::make('itinerary_string')
                    ->searchable(),
                TextColumn::make('fare_basis_out')
                    ->searchable(),
                TextColumn::make('fare_basis_in')
                    ->searchable(),
                TextColumn::make('branch_code')
                    ->searchable(),
                TextColumn::make('office_id')
                    ->searchable(),
                TextColumn::make('created_by_user')
                    ->searchable(),
                TextColumn::make('airline_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('currency_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('supplier_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('sales_user_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('client_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('branch_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('cost_base_amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('cost_tax_amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('cost_total_amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('profit_amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('discount_amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('extra_tax_amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('sale_total_amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('carrier_pnr_carrier')
                    ->searchable(),
                TextColumn::make('carrier_pnr')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            */
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
