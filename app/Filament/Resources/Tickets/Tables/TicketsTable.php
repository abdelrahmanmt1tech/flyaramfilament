<?php

namespace App\Filament\Resources\Tickets\Tables;

use App\Models\AccountStatement;
use App\Models\Branch;
use App\Models\Client;
use App\Models\Franchise;
use App\Models\Supplier;
use App\Models\TaxType;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
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
                    ->label(__('dashboard.fields.ticket_no'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('gds')
                    ->label(__('dashboard.fields.gds'))
                    ->searchable()
                    ->sortable(),

                // Booking Date
                TextColumn::make('booking_date')
                    ->label(__('dashboard.fields.booking_date_label'))
                    ->state(fn($record) => ($record?->booking_date || $record?->issue_date))
                    ->date()
                    ->sortable(),

                // Passenger (أول اسم / عدد)
                TextColumn::make('passengers')
                    ->label(__('dashboard.fields.passenger_label'))
                    ->state(fn($record) => $record->passengers()->limit(3)
                        ->pluck('first_name')->implode(' | ')),

                TextColumn::make('office_id')
                    ->label(__('dashboard.fields.branch_number'))
                    ->prefix(fn($record) => $record?->branch_code)
                    ->sortable(),

                TextColumn::make('created_by_user')
                    ->label(__('dashboard.fields.user_number'))
                    ->sortable(),

                TextColumn::make('ticket_type_code')
                    ->label(__('dashboard.fields.type_code'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->badge(),

                IconColumn::make('is_domestic_flight')
                    ->label(__('dashboard.fields.internal'))
                    ->boolean()
                    ->sortable(),

                TextColumn::make('cost_total_amount')
                    ->label(__('dashboard.fields.cost'))
                    ->money(fn($record) => $record->currency?->symbol ?: 'SAR', true)
                    ->sortable(),

                // Price For Sale (النهائي)
                TextColumn::make('sale_total_amount')
                    ->label(__('dashboard.fields.price_for_sale'))
                    ->badge()
                    ->color(function ($record) {
                        if ($record->sale_total_amount < $record->cost_total_amount) {
                            return "danger";
                        }
                        return "info";
                    })
                    ->money(fn($record) => $record->currency?->symbol ?: 'SAR', true)
                    ->sortable(),

                // Profits
                TextColumn::make('profit_amount')
                    ->label(__('dashboard.fields.profits'))
                    ->badge()
                    ->state(fn($record) => $record->profit_amount ?? "-")
                    ->color(function ($record) {
                        if (!$record->profit_amount) {
                            return "danger";
                        }
                        if ($record->profit_amount < 10) {
                            return "warning";
                        }
                        return "success";
                    })
                    ->money(fn($record) => $record->currency?->symbol ?: 'SAR', true)
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('airline.name')
                    ->label(__('dashboard.fields.airline_label'))
                    ->placeholder(fn($record) => $record->airline_name)
                    ->searchable()
                    ->sortable(),

                // Flights (مسار مختصر)
                TextColumn::make('itinerary_string')
                    ->label(__('dashboard.fields.flights'))
                    ->limit(40)
                    ->tooltip(fn($record) => $record->itinerary_string ?? "-")
                    ->sortable(),

                TextColumn::make('trip_type')
                    ->label(__('dashboard.fields.type'))
                    ->badge()
                    ->colors([
                        'success' => 'ONE-WAY',
                        'warning' => 'ROUND-TRIP',
                        'info' => 'MULTI-SEG',
                    ])
                    ->sortable(),

                // ======== باقي الحقول (togglable) ========

                TextColumn::make('pnr')
                    ->label(__('dashboard.fields.pnr'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->copyable()
                    ->searchable(),

                TextColumn::make('validating_carrier_code')
                    ->label(__('dashboard.fields.validating_carrier_code'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->badge(),

                TextColumn::make('branch_code')
                    ->label(__('dashboard.fields.branch_code'))
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('office_id')
                    ->label(__('dashboard.fields.office_id'))
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_by_user')
                    ->label(__('dashboard.fields.created_by_user'))
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('fare_basis_out')
                    ->label(__('dashboard.fields.fare_basis_out'))
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('fare_basis_in')
                    ->label(__('dashboard.fields.fare_basis_in'))
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('issue_date')
                    ->label(__('dashboard.fields.issue_date'))
                    ->date('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('cost_base_amount')
                    ->label(__('dashboard.fields.cost_base_amount'))
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('cost_tax_amount')
                    ->label(__('dashboard.fields.cost_tax_amount'))
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('discount_amount')
                    ->label(__('dashboard.fields.discount_amount'))
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('extra_tax_amount')
                    ->label(__('dashboard.fields.extra_tax_amount'))
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('carrier_pnr')
                    ->label(__('dashboard.fields.carrier_pnr'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->copyable(),


            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                // Action::make('migrate')
                //     ->label('ترحيل')
                //     ->icon('heroicon-o-arrow-right')
                //     ->form([
                //         Select::make('branch_id')
                //             ->label('ترحيل إلى فرع')
                //             ->options(Branch::pluck('name', 'id'))
                //             ->default(fn($record) => $record?->branch_id) //  الفرع الحالي
                //             ->searchable()
                //             ->preload(),

                //         Select::make('franchise_id')
                //             ->label('ترحيل إلى فرانشايز')
                //             ->options(Franchise::pluck('name', 'id'))
                //             ->default(fn($record) => $record?->franchise_id) //  الفرانشايز الحالي
                //             ->searchable()
                //             ->preload(),

                //         Select::make('client_id')
                //             ->label('ترحيل إلى عميل')
                //             ->options(Client::pluck('name', 'id'))
                //             ->default(fn($record) => $record?->client_id) //  العميل الحالي
                //             ->searchable()
                //             ->preload(),

                //         Select::make('supplier_id')
                //             ->label('ترحيل إلى مورد')
                //             ->options(Supplier::pluck('name', 'id'))
                //             ->default(fn($record) => $record?->supplier_id) //  المورد الحالي
                //             ->searchable()
                //             ->preload(),
                //     ])
                //     ->action(function ($record, array $data) {
                //         if (!empty($data['branch_id'])) {
                //             $record->branch_id = $data['branch_id'];
                //         }
                //         if (!empty($data['franchise_id'])) {
                //             $record->franchise_id = $data['franchise_id'];
                //         }
                //         if (!empty($data['client_id'])) {
                //             $record->client_id = $data['client_id'];
                //         }
                //         if (!empty($data['supplier_id'])) {
                //             $record->supplier_id = $data['supplier_id'];
                //         }
                //         $record->save();
                //         Notification::make()
                //             ->title('تم ترحيل التذكرة')
                //             ->success()
                //             ->send();
                //     })
                //     ->requiresConfirmation()
                //     ->modalHeading('ترحيل التذكرة')
                //     ->modalButton('تنفيذ الترحيل')
                //     ->color('warning'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    //  ترحيل للفرع
                    Action::make('bulkMigrateBranch')
                        ->label('ترحيل للفرع')
                        ->icon('heroicon-o-building-office')
                        ->form([
                            Select::make('branch_id')
                                ->label('اختر الفرع')
                                ->options(Branch::pluck('name', 'id'))
                                ->searchable()
                                ->preload()
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            foreach ($records as $record) {
                                $record->branch_id = $data['branch_id'];
                                $record->save();
                                AccountStatement::logTicket($record, Branch::class, $data['branch_id']);
                            }
                            Notification::make()
                                ->title('تم ترحيل التذاكر لفرع')
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->modalHeading('ترحيل التذاكر لفرع')
                        ->modalButton('تنفيذ الترحيل')
                        ->color('warning')
                        ->bulk()
                        ->deselectRecordsAfterCompletion()
                        ->accessSelectedRecords(),

                    //  ترحيل للفرانشايز
                    Action::make('bulkMigrateFranchise')
                        ->label('ترحيل للفرانشايز')
                        ->icon('heroicon-o-building-storefront')
                        ->form([
                            Select::make('franchise_id')
                                ->label('اختر الفرانشايز')
                                ->options(Franchise::pluck('name', 'id'))
                                ->searchable()
                                ->preload()
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            foreach ($records as $record) {
                                $record->franchise_id = $data['franchise_id'];
                                $record->save();
                                AccountStatement::logTicket($record, Franchise::class, $data['franchise_id']);
                            }
                            Notification::make()
                                ->title('تم ترحيل التذاكر لفرانشايز')
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->modalHeading('ترحيل التذاكر لفرانشايز')
                        ->modalButton('تنفيذ الترحيل')
                        ->color('warning')
                        ->bulk()
                        ->deselectRecordsAfterCompletion()
                        ->accessSelectedRecords(),

                    //  ترحيل للعميل
                    Action::make('bulkMigrateClient')
                        ->label('ترحيل للعميل')
                        ->icon('heroicon-o-user')
                        ->form([
                            Select::make('client_id')
                                ->label('اختر العميل')
                                ->options(Client::pluck('name', 'id'))
                                ->searchable()
                                ->preload()
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            foreach ($records as $record) {
                                $record->client_id = $data['client_id'];
                                $record->save();
                               AccountStatement::logTicket($record, Client::class, $data['client_id']);
                            }
                            Notification::make()
                                ->title('تم ترحيل التذاكر لعميل')
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->modalHeading('ترحيل التذاكر لعميل')
                        ->modalButton('تنفيذ الترحيل')
                        ->color('warning')
                        ->bulk()
                        ->deselectRecordsAfterCompletion()
                        ->accessSelectedRecords(),

                    //  ترحيل للمورد
                    Action::make('bulkMigrateSupplier')
                        ->label('ترحيل للمورد')
                        ->icon('heroicon-o-truck')
                        ->form([
                            Select::make('supplier_id')
                                ->label('اختر المورد')
                                ->options(Supplier::pluck('name', 'id'))
                                ->searchable()
                                ->preload()
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            foreach ($records as $record) {
                                $record->supplier_id = $data['supplier_id'];
                                $record->save();
                               AccountStatement::logTicket($record, Supplier::class, $data['supplier_id'], true);
                            }
                            Notification::make()
                                ->title('تم ترحيل التذاكر لمورد')
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->modalHeading('ترحيل التذاكر لمورد')
                        ->modalButton('تنفيذ الترحيل')
                        ->color('warning')
                        ->bulk()
                        ->deselectRecordsAfterCompletion()
                        ->accessSelectedRecords(),
                    Action::make('bulkEditProfitAndDiscount')
                        ->label('تعديل الربح والخصم')
                        ->icon('heroicon-o-currency-dollar')
                        ->form([
                            // Profit field
                            TextInput::make('profit_amount')
                                ->label('تعديل الربح')
                                ->numeric()
                                ->minValue(0)
                                ->nullable()
                                ->suffix('SAR')
                                ->reactive(),

                            // Tax Type select
                            Select::make('tax_type_id')
                                ->label('نوع الضريبة')
                                ->options(
                                    TaxType::all()->mapWithKeys(function ($tax) {
                                        return [$tax->id => $tax->name . ' (' . $tax->value . '%)'];
                                    })
                                )
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    if ($state && $get('profit_amount')) {
                                        $tax = TaxType::find($state);
                                        if ($tax) {
                                            $profit = $get('profit_amount');
                                            $extraTax = ($profit * $tax->value) / 100;
                                            $set('extra_tax_amount', $extraTax);
                                        }
                                    }
                                }),

                            // Extra Tax field (read-only, auto-calculated)
                            TextInput::make('extra_tax_amount')
                                ->label('قيمه الضريبه من الارباح')
                                ->numeric()
                                ->minValue(0)
                                ->nullable()
                                ->suffix('SAR')
                                ->disabled()
                                ->reactive(), // نجعله للعرض فقط

                            // Discount field
                            TextInput::make('discount_amount')
                                ->label('تعديل الخصم')
                                ->numeric()
                                ->minValue(0)
                                ->nullable()
                                ->suffix('SAR'),
                        ])
                        ->action(function ($records, array $data) {
                            foreach ($records as $record) {
                                if (!empty($data['discount_amount'])) {
                                    $record->discount_amount = $data['discount_amount'];
                                }

                                if (!empty($data['profit_amount'])) {
                                    $record->profit_amount = $data['profit_amount'];
                                }

                                if (!empty($data['tax_type_id'])) {
                                    $tax = TaxType::find($data['tax_type_id']);
                                    $record->tax_type_id = $tax->id;

                                    if (!empty($data['profit_amount']) && $tax) {
                                        $record->extra_tax_amount = ($data['profit_amount'] * $tax->value) / 100;
                                    }
                                }

                                $record->save();
                            }
                            Notification::make()
                                ->title('تم تعديل الربح والخصم')
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->modalHeading('تعديل الربح والخصم')
                        ->modalButton('تنفيذ التعديل')
                        ->color('warning')
                        ->bulk()
                        ->deselectRecordsAfterCompletion()
                        ->accessSelectedRecords(),


                ]),
            ]);
    }
}
