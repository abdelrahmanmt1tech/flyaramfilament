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
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
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
                    ->label(__('dashboard.fields.type_code')),
                    // ->toggleable(isToggledHiddenByDefault: true)
                    // ->badge(),

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
            ->filters([
                TrashedFilter::make(),
            ])
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
                                AccountStatement::logTicket($record, Branch::class, $data['branch_id'], $isCredit , $isVoid ? 'refund' : 'sale');
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
                        ->visible(fn ($livewire) =>
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
                                AccountStatement::logTicket($record, Franchise::class, $data['franchise_id'], $isCredit , $isVoid ? 'refund' : 'sale');
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
                        ->visible(fn ($livewire) =>
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
                                AccountStatement::logTicket($record, Client::class, $data['client_id'], $isCredit , $isVoid ? 'refund' : 'sale');
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
                        ->visible(fn ($livewire) =>
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
                                AccountStatement::logTicket($record, Supplier::class, $data['supplier_id'], $isCredit , $isVoid ? 'refund' : 'sale');
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
                    // ///////////////////

                    // Action::make('addInvoice')
                    //     ->label('اضافة فاتورة بيع')
                    //     ->icon('heroicon-o-document-text')
                    //     ->schema([
                    //         Repeater::make('tickets')
                    //             ->label('تفاصيل التذاكر')
                    //             ->schema([
                    //                 Grid::make(2)
                    //                     ->schema([
                    //                         TextInput::make('ticket_number_core')
                    //                             ->label('رقم التذكرة')
                    //                             ->disabled()
                    //                             ->dehydrated(),

                    //                         TextInput::make('airline_name')
                    //                             ->label('الخطوط الجوية')
                    //                             ->disabled()
                    //                             ->dehydrated(),

                    //                         TextInput::make('total_taxes')
                    //                             ->label('إجمالي الضرائب')
                    //                             ->disabled()
                    //                             ->dehydrated(),

                    //                         TextInput::make('sale_total_amount')
                    //                             ->label('سعر البيع')
                    //                             ->disabled()
                    //                             ->dehydrated(),
                    //                     ]),
                    //             ])
                    //       ->default(function ($livewire) {
                    //         $selectedRecords = $livewire->getSelectedTableRecords();

                    //         if (empty($selectedRecords)) {
                    //             return [];
                    //         }

                    //         // فلترة التذاكر بحيث نأخذ فقط اللي نوعها VOID
                    //         $voidTickets = $selectedRecords->filter(fn($ticket) => $ticket->ticket_type_code !== 'VOID' && $ticket->invoices()->where('type', 'sale')->count() == 0);

                    //         return $voidTickets->map(function ($ticket) {
                    //             $totalTaxes = ($ticket->cost_tax_amount ?? 0) + ($ticket->extra_tax_amount ?? 0);

                    //             return [
                    //                 'ticket_number_core' => $ticket->ticket_number_core,
                    //                 'airline_name'       => $ticket->airline_name,
                    //                 'total_taxes'        => number_format($totalTaxes, 2),
                    //                 'sale_total_amount'  => number_format($ticket->sale_total_amount, 2),
                    //             ];
                    //         })->toArray();
                    //     })

                    //             ->reorderable(false)
                    //             ->addable(false)
                    //             ->deletable(false)
                    //             ->columnSpanFull(),

                    //         Select::make('statementable_type')
                    //             ->label('نوع الجهة')
                    //             ->options([
                    //                 Client::class    => 'عميل',
                    //                 Supplier::class  => 'مورد',
                    //                 Branch::class    => 'فرع',
                    //                 Franchise::class => 'فرانشايز',
                    //             ])
                    //             ->searchable()
                    //             ->native(false)
                    //             ->live()
                    //             ->required()
                    //             ->afterStateUpdated(fn($set) => $set('statementable_id', null)),

                    //         Select::make('statementable_id')
                    //             ->label('الجهة')
                    //             ->options(function (callable $get) {
                    //                 $type = $get('statementable_type');
                    //                 if (!$type) {
                    //                     return [];
                    //                 }

                    //                 return match ($type) {
                    //                     Client::class    => Client::pluck('name', 'id')->toArray(),
                    //                     Supplier::class  => Supplier::pluck('name', 'id')->toArray(),
                    //                     Branch::class    => Branch::pluck('name', 'id')->toArray(),
                    //                     Franchise::class => Franchise::pluck('name', 'id')->toArray(),
                    //                     default          => [],
                    //                 };
                    //             })
                    //             ->searchable()
                    //             ->native(false)
                    //             ->placeholder('اختر الجهة أولاً من نوع الجهة')
                    //             ->required()
                    //             ->disabled(fn(callable $get) => !$get('statementable_type')),

                    //         Textarea::make('notes')
                    //             ->label('ملاحظات')
                    //             ->columnSpanFull(),
                    //     ])
                    //     ->action(function ($records, array $data) {

                    //         // $records = $records->reject(fn($t) => $t->is_invoiced);
                    //         // $records = $records->reject(fn($t) => $t->ticket_type_code == 'VOID');
                    //         $records = $records->reject(fn($t) => $t->ticket_type_code == 'VOID');

                            
                    //         if ($records->isEmpty()) {
                    //             Notification::make()
                    //                 ->title('كل التذاكر المحددة عليها فواتير بالفعل')
                    //                 ->danger()
                    //                 ->send();
                    //             return;
                    //         }


                    //         $totalTaxes  = $records->sum(fn($t) => ($t->cost_tax_amount ?? 0) + ($t->extra_tax_amount ?? 0));
                    //         $totalAmount = $records->sum('sale_total_amount');


                    //         $lastInvoiceId   = Invoice::max('id') ?? 0;
                    //         $invoiceNumber   = 'INV-' . now()->format('Y') . '-' . str_pad($lastInvoiceId + 1, 5, '0', STR_PAD_LEFT);


                    //         $invoice = Invoice::create([
                    //             'type'          => $data['statementable_type'] ==  Supplier::class ?  'purchase' :'sale',
                    //             'is_drafted'    => false,
                    //             'total_taxes'   => $totalTaxes,
                    //             'total_amount'  => $totalAmount,
                    //             'invoice_number' => $invoiceNumber,
                    //             'notes'         => $data['notes'] ?? null,
                    //             'invoiceable_type' => $data['statementable_type'],
                    //             'invoiceable_id' => $data['statementable_id'],
                    //         ]);


                    //         foreach ($records as $ticket) {
                    //             $invoice->tickets()->attach($ticket->id);
                    //         }

                    //         Ticket::whereIn('id', $records->pluck('id'))->update(['is_invoiced' => true]);

                    //         Notification::make()
                    //             ->title('تم إنشاء الفاتورة رقم ' . $invoiceNumber)
                    //             ->success()
                    //             ->send();
                    //     })
                    //     ->requiresConfirmation()
                    //     ->modalWidth('4xl')
                    //     ->modalHeading('إضافة فاتورة')
                    //     ->modalSubmitActionLabel('إنشاء الفاتورة')
                    //     ->color('success')
                    //     ->bulk()
                    //     ->deselectRecordsAfterCompletion()
                    //     ->accessSelectedRecords()
                    //      ->visible(fn ($livewire) =>
                    //         $livewire instanceof \App\Filament\Resources\Tickets\Pages\ListTickets &&
                    //         $livewire->activeTab === 'without_invoices'
                    //     ),



                    // // فواتير استرجاع 
                    // Action::make('bulkRefundInvoices')
                    //     ->label('اضافة فاتورة استرجاع')
                    //     ->icon('heroicon-o-arrow-uturn-left')
                    //     ->schema([
                    //             Repeater::make('tickets')
                    //             ->label('تفاصيل التذاكر')
                    //             ->schema([
                    //                 Grid::make(2)
                    //                     ->schema([
                    //                         TextInput::make('ticket_number_core')
                    //                             ->label('رقم التذكرة')
                    //                             ->disabled()
                    //                             ->dehydrated(),

                    //                         TextInput::make('airline_name')
                    //                             ->label('الخطوط الجوية')
                    //                             ->disabled()
                    //                             ->dehydrated(),

                    //                         TextInput::make('total_taxes')
                    //                             ->label('إجمالي الضرائب')
                    //                             ->disabled()
                    //                             ->dehydrated(),

                    //                         TextInput::make('sale_total_amount')
                    //                             ->label('سعر البيع')
                    //                             ->disabled()
                    //                             ->dehydrated(),
                    //                     ]),
                    //             ])
                    //     ->default(function ($livewire) {
                    //             $selectedRecords = $livewire->getSelectedTableRecords();

                    //             if (empty($selectedRecords)) {
                    //                 return [];
                    //             }

                    //             // فلترة التذاكر بحيث نأخذ فقط اللي نوعها VOID
                    //             $voidTickets = $selectedRecords->filter(fn($ticket) => $ticket->ticket_type_code === 'VOID' && $ticket->invoices()->where('type', 'refund')->count() == 0);

                    //             return $voidTickets->map(function ($ticket) {
                    //                 $totalTaxes = ($ticket->cost_tax_amount ?? 0) + ($ticket->extra_tax_amount ?? 0);
                                        
                    //                 return [
                    //                     'ticket_number_core' => $ticket->ticket_number_core,
                    //                     'airline_name'       => $ticket->airline_name,
                    //                     'total_taxes'        => number_format($totalTaxes, 2),
                    //                     'sale_total_amount'  => number_format($ticket->sale_total_amount, 2),
                    //                 ];
                    //             })->toArray();
                    //         })

                    //             ->reorderable(false)
                    //             ->addable(false)
                    //             ->deletable(false)
                    //             ->columnSpanFull(),

                    //         Select::make('statementable_type')
                    //             ->label('نوع الجهة')
                    //             ->options([
                    //                 Client::class    => 'عميل',
                    //                 Supplier::class  => 'مورد',
                    //                 Branch::class    => 'فرع',
                    //                 Franchise::class => 'فرانشايز',
                    //             ])
                    //             ->searchable()
                    //             ->native(false)
                    //             ->live()
                    //             ->required()
                    //             ->afterStateUpdated(fn($set) => $set('statementable_id', null)),

                    //         Select::make('statementable_id')
                    //             ->label('الجهة')
                    //             ->options(function (callable $get) {
                    //                 $type = $get('statementable_type');
                    //                 if (!$type) {
                    //                     return [];
                    //                 }

                    //                 return match ($type) {
                    //                     Client::class    => Client::pluck('name', 'id')->toArray(),
                    //                     Supplier::class  => Supplier::pluck('name', 'id')->toArray(),
                    //                     Branch::class    => Branch::pluck('name', 'id')->toArray(),
                    //                     Franchise::class => Franchise::pluck('name', 'id')->toArray(),
                    //                     default          => [],
                    //                 };
                    //             })
                    //             ->searchable()
                    //             ->native(false)
                    //             ->placeholder('اختر الجهة أولاً من نوع الجهة')
                    //             ->required()
                    //             ->disabled(fn(callable $get) => !$get('statementable_type')),

                    //         Textarea::make('notes')
                    //             ->label('ملاحظات')
                    //             ->columnSpanFull(),
                    //     ])
                    //     ->action(function ($records, array $data) {

                    //         // $records = $records->reject(fn($t) => $t->is_invoiced);
                    //         // $records = $records->reject(fn($t) => $t->ticket_type_code == 'VOID');
                    //         $records = $records->reject(fn($t) =>  $t->ticket_type_code !== 'VOID');

                    //         // foreach ($records as $record) {
                    //         //    $isSupplier = $data['statementable_type'] == Supplier::class; //عشان دي استرجاع ف المدين هيكون
                    //         //     AccountStatement::logTicket($record, $data['statementable_type'], $data['statementable_id'] , !$isSupplier);
                    //         // }

                            
                    //         if ($records->isEmpty()) {
                    //             Notification::make()
                    //                 ->title('كل التذاكر المحددة عليها فواتير بالفعل او تذاكر غير مسترجعة' )
                    //                 ->danger()
                    //                 ->send();
                    //             return;
                    //         }


                    //         $totalTaxes  = $records->sum(fn($t) => ($t->cost_tax_amount ?? 0) + ($t->extra_tax_amount ?? 0));
                    //         $totalAmount = $records->sum('sale_total_amount');
                    //         $totalProfit  = $records->sum('profit_amount');
                    //          $totalAmount -= $totalProfit;


                    //         $lastInvoiceId   = Invoice::max('id') ?? 0;
                    //         $invoiceNumber   = 'INV-' . now()->format('Y') . '-' . str_pad($lastInvoiceId + 1, 5, '0', STR_PAD_LEFT);


                    //         $invoice = Invoice::create([
                    //             'type'          => 'refund',
                    //             'is_drafted'    => false,
                    //             'total_taxes'   => $totalTaxes,
                    //             'total_amount'  => $totalAmount,
                    //             'invoice_number' => $invoiceNumber,
                    //             'notes'         => $data['notes'] ?? null,
                    //             'invoiceable_type' => $data['statementable_type'],
                    //             'invoiceable_id' => $data['statementable_id'],
                    //         ]);


                    //         foreach ($records as $ticket) {
                    //             $invoice->tickets()->attach($ticket->id);
                    //         }

                    //         Ticket::whereIn('id', $records->pluck('id'))->update(['is_invoiced' => true]);

                    //         Notification::make()
                    //             ->title('فواتير الاسترجاع')
                    //             ->body('تم إنشاء فواتير الاسترجاع بنجاح')
                    //             ->success()
                    //             ->send();
                    //     })
                    //     ->requiresConfirmation()
                    //     ->modalWidth('4xl')
                    //     ->modalHeading('إنشاء فاتورة استرجاع')
                    //     ->modalSubmitActionLabel('إنشاء فواتير الاسترجاع')
                    //     ->color('danger')
                    //     ->bulk()
                    //     ->deselectRecordsAfterCompletion()
                    //     ->accessSelectedRecords()
                    //      ->visible(fn ($livewire) =>
                    //         $livewire instanceof \App\Filament\Resources\Tickets\Pages\ListTickets &&
                    //         $livewire->activeTab === 'with_invoices'
                    //     ),

                ]),
            ]);
    }
}
