<?php

namespace App\Filament\Resources\Tickets\Tables;

use App\Models\AccountStatement;
use App\Models\Branch;
use App\Models\Client;
use App\Models\Franchise;
use App\Models\Invoice;
use App\Models\Supplier;
use App\Models\TaxType;
use App\Models\Ticket;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

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

                TextColumn::make('ticket_type_code')
                    ->label(__('dashboard.fields.type_code'))

                    ->formatStateUsing(
                        fn($record) =>
                        $record->ticket_type_code . "( $record->ticket_type )"
                    ),
                // ->toggleable(isToggledHiddenByDefault: true)
                // ->badge(),




                TextColumn::make('gds')
                    ->label(__('dashboard.fields.gds'))
                    ->searchable()
                    ->sortable(),


                // Booking Date
                TextColumn::make('booking_date')
                    ->label(__('dashboard.fields.booking_date_label'))
                    ->state(fn($record) => $record?->booking_date ?? $record?->issue_date)
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



                IconColumn::make('is_domestic_flight')
                    ->label(__('dashboard.fields.internal'))
                    ->boolean()
                    ->sortable(),

                TextColumn::make('assigned_to')
                    ->label('مرحلة لـ')
                    ->state(function ($record) {
                        if ($record->client_id) {
                            return 'عميل: ' . optional($record->client)->name;
                        } elseif ($record->branch_id) {
                            return 'فرع: ' . optional($record->branch)->name;
                        } elseif ($record->franchise_id) {
                            return 'فرانشايز: ' . optional($record->franchise)->name;
                        }
                        return 'غير محدد';
                    })
                    ->color(fn($record) => match (true) {
                        $record->client_id => 'info',
                        $record->branch_id => 'success',
                        $record->franchise_id => 'warning',
                        default => 'gray',
                    })
                    ->sortable()
                    ->searchable(),


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

                TextColumn::make('pnr')->prefix("pnr")
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

            ///////////////////////////////////////////////////////////////////////////////
            ->filters([
                Filter::make('booking_date_range')
                    ->label('تاريخ الحجز')
                    ->schema([
                        DatePicker::make('from')
                            ->label('من تاريخ')
                            ->placeholder('من تاريخ'),
                        DatePicker::make('to')
                            ->label('إلى تاريخ')
                            ->placeholder('إلى تاريخ'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['from'], fn($q) => $q->whereDate('booking_date', '>=', $data['from']))
                            ->when($data['to'], fn($q) => $q->whereDate('booking_date', '<=', $data['to']));
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (!$data['from'] && !$data['to']) return null;
                        $indicators = [];
                        if ($data['from']) $indicators[] = 'من: ' . $data['from'];
                        if ($data['to']) $indicators[] = 'إلى: ' . $data['to'];
                        return implode(' - ', $indicators);
                    }),

                SelectFilter::make('ticket_type_code')
                    ->label(__('dashboard.fields.type_code'))
                    ->options(function () {
                        return Ticket::query()
                            ->distinct()
                            ->whereNotNull('ticket_type_code')
                            ->orderBy('ticket_type_code')
                            ->pluck('ticket_type_code', 'ticket_type_code')
                            ->toArray();
                    })
                    ->multiple()
                    ->searchable()
                    ->preload(),

                SelectFilter::make('gds')
                    ->label(__('dashboard.fields.gds'))
                    ->options(function () {
                        return Ticket::query()
                            ->distinct()
                            ->whereNotNull('gds')
                            ->orderBy('gds')
                            ->pluck('gds', 'gds')
                            ->map(fn($gds) => strtoupper($gds))
                            ->toArray();
                    })
                    ->multiple()
                    ->searchable()
                    ->preload(),

                SelectFilter::make('airline_id')
                    ->label(__('dashboard.fields.airline_label'))
                    ->relationship('airline', 'name')
                    ->searchable()
                    ->preload(),

                TernaryFilter::make('is_domestic_flight')
                    ->label('داخلية/خارجية')
                    ->placeholder('الكل')
                    ->trueLabel('داخلية')
                    ->falseLabel('خارجية'),

                Filter::make('assigned_to')
                    ->label('مرحلة لـ')
                    ->schema([
                        Select::make('client_id')
                            ->label('العميل')
                            ->relationship('client', 'name')
                            ->searchable()
                            ->preload(),
                        Select::make('branch_id')
                            ->label('الفرع')
                            ->relationship('branch', 'name')
                            ->searchable()
                            ->preload(),
                        Select::make('franchise_id')
                            ->label('الفرانشايز')
                            ->relationship('franchise', 'name')
                            ->searchable()
                            ->preload(),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['client_id'], fn($q, $id) => $q->where('client_id', $id))
                            ->when($data['branch_id'], fn($q, $id) => $q->where('branch_id', $id))
                            ->when($data['franchise_id'], fn($q, $id) => $q->where('franchise_id', $id));
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['client_id']) {
                            $client = Client::find($data['client_id']);
                            $indicators[] = 'عميل: ' . ($client?->name ?? 'غير معروف');
                        }
                        if ($data['branch_id']) {
                            $branch = Branch::find($data['branch_id']);
                            $indicators[] = 'فرع: ' . ($branch?->name ?? 'غير معروف');
                        }
                        if ($data['franchise_id']) {
                            $franchise = Franchise::find($data['franchise_id']);
                            $indicators[] = 'فرانشايز: ' . ($franchise?->name ?? 'غير معروف');
                        }
                        return $indicators;
                    }),

                SelectFilter::make('supplier_id')
                    ->label('المورد')
                    ->relationship('supplier', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('trip_type')
                    ->label(__('dashboard.fields.type'))
                    ->options(function () {
                        return Ticket::query()
                            ->distinct()
                            ->whereNotNull('trip_type')
                            ->orderBy('trip_type')
                            ->pluck('trip_type', 'trip_type')
                            ->toArray();
                    })
                    ->multiple(),


            ])
            ///////////////////////////////////////////////////////////////////////////////
            ->recordActions([
                Action::make('viewInvoice')
                    ->label('الفاتورة')
                    ->icon('heroicon-o-document-text')
                    ->color('success')
                    ->visible(fn($record) => $record->invoices()->where('type', 'sale')->exists())
                    ->url(fn($record) => route('invoices.print', $record->invoices()->where('type', 'sale')->first()->slug))
                    ->openUrlInNewTab(),


                // عرض فاتورة الاسترجاع
                Action::make('showRefundInvoice')
                    ->label('فاتورة الاسترجاع')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->visible(fn($record) => $record->invoices()->where('type', 'refund')->exists())
                    ->url(fn($record) => route('invoices.print', $record->invoices()->where('type', 'refund')->first()->slug))
                    ->openUrlInNewTab(),

                ViewAction::make(),
                // -----------------------------------------
                Action::make('editProfitAndDiscount')
                    ->label('تعديل الربح')
                    ->icon('heroicon-o-currency-dollar')
                    ->color('info')
                    ->modalHeading('تعديل الربح والخصم للتذكرة')
                    ->schema([
                        // تعديل الربح
                        TextInput::make('profit_amount')
                            ->label('تعديل الربح')
                            ->default(fn($record) => $record->profit_amount)
                            ->numeric()
                            ->minValue(0)
                            ->suffix('SAR')
                            ->reactive(),

                        // نوع الضريبة
                        Select::make('tax_type_id')
                            ->label('نوع الضريبة')
                            ->visible(fn($record) => !$record->is_domestic_flight)
                            ->default(fn($record) => $record->tax_type_id)
                            ->options(
                                TaxType::whereNotIn('id', [1, 2])->get()->mapWithKeys(function ($tax) {
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

                        // قيمة الضريبة من الأرباح (تُحسب تلقائيًا)
                        TextInput::make('extra_tax_amount')
                            ->label('قيمة الضريبة من الأرباح')
                            ->default(fn($record) => $record->extra_tax_amount)
                            ->numeric()
                            ->suffix('SAR')
                            ->disabled()
                            ->reactive(),

                        // تعديل الخصم
                        TextInput::make('discount_amount')
                            ->label('تعديل الخصم')
                            ->default(fn($record) => $record->discount_amount)
                            ->numeric()
                            ->minValue(0)
                            ->suffix('SAR')
                            ->nullable(),
                    ])
                    ->action(function ($record, array $data) {
                        if (!empty($data['discount_amount'])) {
                            $record->discount_amount = $data['discount_amount'];
                        }

                        if (!empty($data['profit_amount'])) {
                            $record->profit_amount = $data['profit_amount'];
                        }

                        // تطبيق الضريبة فقط إذا لم تكن التذكرة داخلية
                        if (!$record->is_domestic_flight && !empty($data['tax_type_id'])) {
                            $tax = TaxType::find($data['tax_type_id']);
                            $record->tax_type_id = $tax->id;

                            if (!empty($data['profit_amount']) && $tax) {
                                $record->extra_tax_amount = ($data['profit_amount'] * $tax->value) / 100;
                            }
                        }

                        $record->save();

                        Notification::make()
                            ->title('تم تعديل الربح والخصم بنجاح')
                            ->success()
                            ->send();
                    }),

                // -----------------------------------------
                EditAction::make(),
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
                        ->schema([
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
                                $isVoid = $record->ticket_type_code == 'VOID' ? true : false;
                                $isCredit = $isVoid ? true : false; //لو التذكرة نوعها استرجاع
                                Log::info('record', ['isCredit' => $isCredit]);
                                AccountStatement::logTicket($record, Branch::class, $data['branch_id'], $isCredit, $isVoid ? 'refund' : 'sale');
                            }
                            Notification::make()
                                ->title('تم ترحيل التذاكر لفرع')
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->modalHeading('ترحيل التذاكر لفرع')
                        ->modalSubmitActionLabel('تنفيذ الترحيل')
                        ->color('warning')
                        ->bulk()
                        ->deselectRecordsAfterCompletion()
                        ->accessSelectedRecords()
                        ->visible(
                            fn($livewire) =>
                            $livewire instanceof \App\Filament\Resources\Tickets\Pages\ListTickets &&
                                $livewire->activeTab === 'without_users'
                        ),

                    //  ترحيل للفرانشايز
                    Action::make('bulkMigrateFranchise')
                        ->label('ترحيل للفرانشايز')
                        ->icon('heroicon-o-building-storefront')
                        ->schema([
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
                                $isVoid = $record->ticket_type_code == 'VOID' ? true : false;
                                $isCredit = $isVoid ? true : false;
                                AccountStatement::logTicket($record, Franchise::class, $data['franchise_id'], $isCredit, $isVoid ? 'refund' : 'sale');
                            }
                            Notification::make()
                                ->title('تم ترحيل التذاكر لفرانشايز')
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->modalHeading('ترحيل التذاكر لفرانشايز')
                        ->modalSubmitActionLabel('تنفيذ الترحيل')
                        ->color('warning')
                        ->bulk()
                        ->deselectRecordsAfterCompletion()
                        ->accessSelectedRecords()
                        ->visible(
                            fn($livewire) =>
                            $livewire instanceof \App\Filament\Resources\Tickets\Pages\ListTickets &&
                                $livewire->activeTab === 'without_users'
                        ),

                    //  ترحيل للعميل
                    Action::make('bulkMigrateClient')
                        ->label('ترحيل للعميل')
                        ->icon('heroicon-o-user')
                        ->schema([
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
                                $isVoid = $record->ticket_type_code == 'VOID' ? true : false;
                                $isCredit = $isVoid ? true : false;
                                AccountStatement::logTicket($record, Client::class, $data['client_id'], $isCredit, $isVoid ? 'refund' : 'sale');
                            }
                            Notification::make()
                                ->title('تم ترحيل التذاكر لعميل')
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->modalHeading('ترحيل التذاكر لعميل')
                        ->modalSubmitActionLabel('تنفيذ الترحيل')
                        ->color('warning')
                        ->bulk()
                        ->deselectRecordsAfterCompletion()
                        ->accessSelectedRecords()
                        ->visible(
                            fn($livewire) =>
                            $livewire instanceof \App\Filament\Resources\Tickets\Pages\ListTickets &&
                                $livewire->activeTab === 'without_users'
                        ),



                    //  ترحيل للمورد
                    Action::make('bulkMigrateSupplier')
                        ->label('تغيير المورد')
                        ->icon('heroicon-o-truck')
                        ->schema([

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
                                $isVoid = $record->ticket_type_code == 'VOID' ? true : false;
                                $isCredit =   $isVoid ? false : true;
                                AccountStatement::logTicket($record, Supplier::class, $data['supplier_id'], $isCredit, $isVoid ? 'refund' : 'sale');
                            }
                            Notification::make()
                                ->title('تم ترحيل التذاكر لمورد')
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->modalHeading('ترحيل التذاكر لمورد')
                        ->modalSubmitActionLabel('تنفيذ الترحيل')
                        ->color('secondary')
                        ->bulk()
                        ->deselectRecordsAfterCompletion()
                        ->accessSelectedRecords(),




                    Action::make('bulkEditProfitAndDiscount')
                        ->label('تعديل الربح والخصم')
                        ->icon('heroicon-o-currency-dollar')
                        ->schema([
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
                                ->helperText('لن يتم تطبيق هذه الضريبة على التذاكر الداخلية')
                                ->options(
                                    TaxType::whereNotIn('id', [1, 2])->get()->mapWithKeys(function ($tax) {
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

                                if (!$record->is_domestic_flight && !empty($data['tax_type_id'])) {
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
                        ->modalSubmitActionLabel('تنفيذ التعديل')
                        ->color('info')
                        ->bulk()
                        ->deselectRecordsAfterCompletion()
                        ->accessSelectedRecords(),


                ]),
            ]);
    }
}
