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

                TextColumn::make('pnr') ->prefix("pnr")
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
                    ->label('عرض الفاتورة')
                    ->icon('heroicon-o-document-text')
                    ->visible(fn($record) => $record->invoices()->exists())
                    ->url(fn($record) => route('invoices.print', $record->invoices()->first()->id))
                    ->openUrlInNewTab(),
                ViewAction::make(),
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
                                AccountStatement::logTicket($record, Branch::class, $data['branch_id']);
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
                        ->accessSelectedRecords(),

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
                                AccountStatement::logTicket($record, Franchise::class, $data['franchise_id']);
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
                        ->accessSelectedRecords(),

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
                                AccountStatement::logTicket($record, Client::class, $data['client_id']);
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
                        ->accessSelectedRecords(),



                    //  ترحيل للمورد
                    Action::make('bulkMigrateSupplier')
                        ->label('ترحيل للمورد')
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
                                AccountStatement::logTicket($record, Supplier::class, $data['supplier_id'], true);
                            }
                            Notification::make()
                                ->title('تم ترحيل التذاكر لمورد')
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->modalHeading('ترحيل التذاكر لمورد')
                        ->modalSubmitActionLabel('تنفيذ الترحيل')
                        ->color('warning')
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
                        ->modalSubmitActionLabel('تنفيذ التعديل')
                        ->color('info')
                        ->bulk()
                        ->deselectRecordsAfterCompletion()
                        ->accessSelectedRecords(),
                    // ///////////////////

                    Action::make('addInvoice')
                        ->label('اضافة فاتورة')
                        ->icon('heroicon-o-document-text')
                        ->schema([
                            Repeater::make('tickets')
                                ->label('تفاصيل التذاكر')
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            TextInput::make('ticket_number_core')
                                                ->label('رقم التذكرة')
                                                ->disabled()
                                                ->dehydrated(),

                                            TextInput::make('airline_name')
                                                ->label('الخطوط الجوية')
                                                ->disabled()
                                                ->dehydrated(),

                                            TextInput::make('total_taxes')
                                                ->label('إجمالي الضرائب')
                                                ->disabled()
                                                ->dehydrated(),

                                            TextInput::make('sale_total_amount')
                                                ->label('سعر البيع')
                                                ->disabled()
                                                ->dehydrated(),
                                        ]),
                                ])
                                ->default(function ($livewire) {
                                    $selectedRecords = $livewire->getSelectedTableRecords();

                                    if (empty($selectedRecords)) {
                                        return [];
                                    }

                                    return $selectedRecords->map(function ($ticket) {
                                        $totalTaxes = ($ticket->cost_tax_amount ?? 0) + ($ticket->extra_tax_amount ?? 0);

                                        return [
                                            'ticket_number_core' => $ticket->ticket_number_core,
                                            'airline_name'       => $ticket->airline_name,
                                            'total_taxes'        => number_format($totalTaxes, 2),
                                            'sale_total_amount'  => number_format($ticket->sale_total_amount, 2),
                                        ];
                                    })->toArray();
                                })
                                ->reorderable(false)
                                ->addable(false)
                                ->deletable(false)
                                ->columnSpanFull(),

                            Select::make('statementable_type')
                                ->label('نوع الجهة')
                                ->options([
                                    Client::class    => 'عميل',
                                    Supplier::class  => 'مورد',
                                    Branch::class    => 'فرع',
                                    Franchise::class => 'فرانشايز',
                                ])
                                ->searchable()
                                ->native(false)
                                ->live()
                                ->required()
                                ->afterStateUpdated(fn($set) => $set('statementable_id', null)),

                            Select::make('statementable_id')
                                ->label('الجهة')
                                ->options(function (callable $get) {
                                    $type = $get('statementable_type');
                                    if (!$type) {
                                        return [];
                                    }

                                    return match ($type) {
                                        Client::class    => Client::pluck('name', 'id')->toArray(),
                                        Supplier::class  => Supplier::pluck('name', 'id')->toArray(),
                                        Branch::class    => Branch::pluck('name', 'id')->toArray(),
                                        Franchise::class => Franchise::pluck('name', 'id')->toArray(),
                                        default          => [],
                                    };
                                })
                                ->searchable()
                                ->native(false)
                                ->placeholder('اختر الجهة أولاً من نوع الجهة')
                                ->required()
                                ->disabled(fn(callable $get) => !$get('statementable_type')),

                            Textarea::make('notes')
                                ->label('ملاحظات')
                                ->columnSpanFull(),
                        ])
                        ->action(function ($records, array $data) {

                            $records = $records->reject(fn($t) => $t->is_invoiced);


                            if ($records->isEmpty()) {
                                Notification::make()
                                    ->title('كل التذاكر المحددة عليها فواتير بالفعل')
                                    ->danger()
                                    ->send();
                                return;
                            }


                            $totalTaxes  = $records->sum(fn($t) => ($t->cost_tax_amount ?? 0) + ($t->extra_tax_amount ?? 0));
                            $totalAmount = $records->sum('sale_total_amount');


                            $lastInvoiceId   = Invoice::max('id') ?? 0;
                            $invoiceNumber   = 'INV-' . now()->format('Y') . '-' . str_pad($lastInvoiceId + 1, 5, '0', STR_PAD_LEFT);


                            $invoice = Invoice::create([
                                'type'          => 'sale',
                                'is_drafted'    => false,
                                'total_taxes'   => $totalTaxes,
                                'total_amount'  => $totalAmount,
                                'invoice_number' => $invoiceNumber,
                                'notes'         => $data['notes'] ?? null,
                                'invoiceable_type' => $data['statementable_type'],
                                'invoiceable_id' => $data['statementable_id'],
                            ]);


                            foreach ($records as $ticket) {
                                $invoice->tickets()->attach($ticket->id);
                            }

                            Ticket::whereIn('id', $records->pluck('id'))->update(['is_invoiced' => true]);

                            Notification::make()
                                ->title('تم إنشاء الفاتورة رقم ' . $invoiceNumber)
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->modalWidth('4xl')
                        ->modalHeading('إضافة فاتورة')
                        ->modalSubmitActionLabel('إنشاء الفاتورة')
                        ->color('success')
                        ->bulk()
                        ->deselectRecordsAfterCompletion()
                        ->accessSelectedRecords(),



                    // فواتير استرجاع 
                    Action::make('bulkRefundInvoices')
                        ->label('فاتورة استرجاع')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->schema([
                            Repeater::make('tickets')
                                ->label('التذاكر المحددة')
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            TextInput::make('ticket_number_core')
                                                ->label('رقم التذكرة')
                                                ->disabled()
                                                ->dehydrated(),

                                            TextInput::make('airline_name')
                                                ->label('الخطوط الجوية')
                                                ->disabled()
                                                ->dehydrated(),

                                            TextInput::make('invoice_number')
                                                ->label('رقم الفاتورة الأصلية')
                                                ->disabled()
                                                ->dehydrated(),

                                            TextInput::make('sale_total_amount')
                                                ->label('سعر البيع')
                                                ->disabled()
                                                ->dehydrated(),
                                        ]),
                                ])
                                ->default(function ($livewire) {
                                    $selectedRecords = $livewire->getSelectedTableRecords();

                                    if (empty($selectedRecords)) {
                                        return [];
                                    }

                                    return $selectedRecords->map(function ($ticket) {
                                        $originalInvoice = $ticket->invoices()->first();

                                        return [
                                            'ticket_number_core' => $ticket->ticket_number_core,
                                            'airline_name'       => $ticket->airline_name,
                                            'invoice_number'     => $originalInvoice ? $originalInvoice->invoice_number : 'لا توجد فاتورة',
                                            'sale_total_amount'  => number_format($ticket->sale_total_amount, 2),
                                            'ticket_id'          => $ticket->id,
                                            'has_invoice'        => !is_null($originalInvoice),
                                            'has_refund_invoice' => $ticket->invoices()->where('type', 'refund')->exists(),
                                        ];
                                    })->toArray();
                                })
                                ->reorderable(false)
                                ->addable(false)
                                ->deletable(false)
                                ->columnSpanFull(),

                            Textarea::make('notes')
                                ->label('ملاحظات الاسترجاع')
                                ->columnSpanFull(),
                        ])
                        ->action(function ($records, array $data) {
                            // تصفية التذاكر التي لها فواتير ولا توجد لها فواتير استرجاع
                            $ticketsWithInvoices = $records->filter(function ($ticket) {
                                $hasOriginalInvoice = $ticket->invoices()->where('type', '!=', 'refund')->exists();
                                $hasRefundInvoice = $ticket->invoices()->where('type', 'refund')->exists();

                                return $hasOriginalInvoice && !$hasRefundInvoice;
                            });

                            if ($ticketsWithInvoices->isEmpty()) {
                                Notification::make()
                                    ->title('لا توجد تذاكر مؤهلة للاسترجاع')
                                    ->body('جميع التذاكر المحددة إما ليس لها فواتير أصلية أو لها فواتير استرجاع مسبقة')
                                    ->danger()
                                    ->send();
                                return;
                            }

                            // تجميع التذاكر حسب الفاتورة الأصلية
                            $invoicesGroup = [];
                            foreach ($ticketsWithInvoices as $ticket) {
                                $originalInvoice = $ticket->invoices()->where('type', '!=', 'refund')->first();

                                if ($originalInvoice) {
                                    $invoiceKey = $originalInvoice->invoiceable_type . '_' . $originalInvoice->invoiceable_id;

                                    if (!isset($invoicesGroup[$invoiceKey])) {
                                        $invoicesGroup[$invoiceKey] = [
                                            'invoiceable_type' => $originalInvoice->invoiceable_type,
                                            'invoiceable_id' => $originalInvoice->invoiceable_id,
                                            'tickets' => [],
                                            'total_amount' => 0,
                                            'total_taxes' => 0,
                                        ];
                                    }

                                    $invoicesGroup[$invoiceKey]['tickets'][] = $ticket;
                                    $invoicesGroup[$invoiceKey]['total_amount'] += $ticket->sale_total_amount;
                                    $invoicesGroup[$invoiceKey]['total_taxes'] += ($ticket->cost_tax_amount ?? 0) + ($ticket->extra_tax_amount ?? 0);
                                }
                            }

                            $createdRefundInvoices = [];

                            // إنشاء فواتير الاسترجاع
                            foreach ($invoicesGroup as $group) {
                                // إنشاء رقم فاتورة الاسترجاع
                                $lastInvoiceId = Invoice::max('id') ?? 0;
                                $refundInvoiceNumber = 'REF-' . now()->format('Y') . '-' . str_pad($lastInvoiceId + 1, 5, '0', STR_PAD_LEFT);

                                // إنشاء فاتورة الاسترجاع
                                $refundInvoice = Invoice::create([
                                    'type' => 'refund',
                                    'is_drafted' => false,
                                    'total_taxes' => $group['total_taxes'],
                                    'total_amount' => $group['total_amount'],
                                    'invoice_number' => $refundInvoiceNumber,
                                    'reference_num' => $originalInvoice ? $originalInvoice->invoice_number : null,
                                    'notes' => $data['notes'] ?? null,
                                    'invoiceable_type' => $group['invoiceable_type'],
                                    'invoiceable_id' => $group['invoiceable_id'],
                                ]);

                                // ربط التذاكر بفاتورة الاسترجاع
                                foreach ($group['tickets'] as $ticket) {
                                    $refundInvoice->tickets()->attach($ticket->id);

                                    // تحديث حالة التذكرة
                                    $ticket->update(['is_refunded' => true]);
                                }

                                $createdRefundInvoices[] = $refundInvoiceNumber;
                            }

                            // إحصاءات
                            $skippedTickets = $records->count() - $ticketsWithInvoices->count();

                            $message = 'تم إنشاء ' . count($createdRefundInvoices) . ' فاتورة استرجاع: ' . implode(', ', $createdRefundInvoices);

                            if ($skippedTickets > 0) {
                                $message .= ' (تم تخطي ' . $skippedTickets . ' تذكرة)';
                            }

                            Notification::make()
                                ->title('فواتير الاسترجاع')
                                ->body($message)
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->modalWidth('4xl')
                        ->modalHeading('إنشاء فاتورة استرجاع')
                        ->modalSubmitActionLabel('إنشاء فواتير الاسترجاع')
                        ->color('danger')
                        ->bulk()
                        ->deselectRecordsAfterCompletion()
                        ->accessSelectedRecords()

                ]),
            ]);
    }
}
