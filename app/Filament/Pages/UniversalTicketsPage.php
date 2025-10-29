<?php

namespace App\Filament\Pages;

use App\Models\Ticket;
use App\Models\Invoice;
use App\Models\Client;
use App\Models\Supplier;
use App\Models\Branch;
use App\Models\Franchise;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Pages\Page;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class UniversalTicketsPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected  string $view = 'filament.pages.universal-tickets';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationLabel = 'تذاكر الكيانات';

    protected static ?string $title = 'تذاكر الكيانات';

    /**
     * @return bool
     */
    public static function isShouldRegisterNavigation(): bool
    {
        return false ;
    }

//    public static function getNavigationGroup(): ?string
//    {
//        return 'التذاكر';
//    }

    protected static ?int $navigationSort = 71;

    public ?string $entityType = null;
    public ?int $entityId = null;
    public ?Model $entity = null;
    public ?string $activeTab = 'without_invoice';

    protected $queryString = [
        'activeTab' => ['except' => 'without_invoice'],
        'tableSortColumn',
        'tableSortDirection',
        'tableFilters',
    ];

    public function mount(): void
    {
        $this->entityType = request()->query('type');
        $this->entityId = request()->query('id');

        if ($this->entityType && $this->entityId) {
            $this->entity = $this->resolveEntity($this->entityType, $this->entityId);
        }
    }

    public function getHeading(): string
    {
        return $this->getPageTitle();
    }

    protected function getPageTitle(): string
    {
        if (!$this->entityType || !$this->entityId) {
            return 'جميع التذاكر';
        }

        $titles = [
            'branch' => "تذاكر الفرع",
            'client' => "تذاكر العميل",
            'franchise' => "تذاكر الامتياز",
            'supplier' => "تذاكر المورد",
        ];

        $entityName = $this->entity?->name ?? $this->entity?->company_name ?? 'غير معروف';

        return ($titles[$this->entityType] ?? 'تذاكر') . " - {$entityName}";
    }

    protected function resolveEntity(string $type, int $id): ?Model
    {
        return match ($type) {
            'branch' => \App\Models\Branch::find($id),
            'client' => \App\Models\Client::find($id),
            'franchise' => \App\Models\Franchise::find($id),
            'supplier' => \App\Models\Supplier::find($id),
            default => null,
        };
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(function () {
                $query = Ticket::query()->with([
                    'currency',
                    'airline',
                    'branch',
                    'client',
                    'franchise',
                    'supplier',
                    'invoices'
                ]);

                if ($this->entityType && $this->entityId) {
                    $query->where("{$this->entityType}_id", $this->entityId);
                }

                $query = $this->applyTabFilter($query);

                return $query;
            })
            ->columns([
                TextColumn::make('ticket_number_core')
                    ->label(__('dashboard.fields.ticket_no'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('gds')
                    ->label(__('dashboard.fields.gds'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('booking_date')
                    ->label(__('dashboard.fields.booking_date_label'))
                    ->state(fn($record) => ($record?->booking_date || $record?->issue_date))
                    ->date()
                    ->sortable(),

                TextColumn::make('passengers')
                    ->label(__('dashboard.fields.passenger_label'))
                    ->state(fn($record) => $record->passengers()->limit(3)
                        ->pluck('first_name')->implode(' | ')),

                TextColumn::make('invoice_status')
                    ->label('حالة الفاتورة')
                    ->badge()
                    ->state(function ($record) {
                        if ($record->invoices->isEmpty()) {
                            return 'بدون فاتورة';
                        }

                        $invoice = $record->invoices->first();
                        return match ($invoice->type) {
                            'sale' => 'بيع',
                            'purchase' => 'شراء',
                            'refund' => 'استرجاع',
                            default => $invoice->type
                        };
                    })
                    ->colors([
                        'gray' => 'بدون فاتورة',
                        'success' => 'بيع',
                        'primary' => 'شراء',
                        'warning' => 'استرجاع',
                    ]),

                TextColumn::make('office_id')
                    ->label(__('dashboard.fields.branch_number'))
                    ->prefix(fn($record) => $record?->branch_code)
                    ->sortable(),

                TextColumn::make('created_by_user')
                    ->label(__('dashboard.fields.user_number'))
                    ->sortable(),

                TextColumn::make('ticket_type_code')
                    ->label(__('dashboard.fields.type_code')),

                IconColumn::make('is_domestic_flight')
                    ->label(__('dashboard.fields.internal'))
                    ->boolean()
                    ->sortable(),

                TextColumn::make('cost_total_amount')
                    ->label(__('dashboard.fields.cost'))
                    ->money(fn($record) => $record->currency?->symbol ?: 'SAR', true)
                    ->sortable(),

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
                    ->sortable(),

                TextColumn::make('airline.name')
                    ->label(__('dashboard.fields.airline_label'))
                    ->placeholder(fn($record) => $record->airline_name)
                    ->searchable()
                    ->sortable(),

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
            ])
            ->filters([
                // يمكن إضافة فلاتر إضافية
            ])
            ->actions([
                Action::make('viewSaleInvoice')
                    ->label('فاتورة بيع')
                    ->icon('heroicon-o-document-text')
                    ->color('success')
                    ->visible(fn($record) => $record->invoices()->where('type', 'sale')->exists())
                    ->url(fn($record) => route('invoices.print', $record->invoices()->where('type', 'sale')->first()->slug))
                    ->openUrlInNewTab(),
                Action::make('viewPurchaseInvoice')
                    ->label('فاتورة شراء')
                    ->icon('heroicon-o-document-text')
                    ->color('success')
                    ->visible(fn($record) => $record->invoices()->where('type', 'purchase')->exists())
                    ->url(fn($record) => route('invoices.print', $record->invoices()->where('type', 'purchase')->first()->slug))
                    ->openUrlInNewTab(),
                Action::make('showRefundInvoice')
                    ->label('فاتورة الاسترجاع')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->visible(fn($record) => $record->invoices()->where('type', 'refund')->exists())
                    ->url(fn($record) => route('invoices.print', $record->invoices()->where('type', 'refund')->first()->slug))
                    ->openUrlInNewTab(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // فاتورة بيع
                    Action::make('addSaleInvoice')
                        ->label('اضافة فاتورة بيع')
                        ->icon('heroicon-o-document-text')
                        ->visible(fn() => $this->activeTab === 'without_invoice' && in_array($this->entityType, ['client', 'branch', 'franchise']))
                        ->schema([
                            Repeater::make('tickets')
                                ->label('تفاصيل التذاكر')
                                ->schema([
                                    Grid::make(2)->schema([
                                        TextInput::make('ticket_number_core')->label('رقم التذكرة')->disabled()->dehydrated(),
                                        TextInput::make('airline_name')->label('الخطوط الجوية')->disabled()->dehydrated(),
                                        TextInput::make('total_taxes')->label('إجمالي الضرائب')->disabled()->dehydrated(),
                                        TextInput::make('sale_total_amount')->label('سعر البيع')->disabled()->dehydrated(),
                                    ]),
                                ])
                                ->default(function ($livewire) {
                                    $selectedRecords = $livewire->getSelectedTableRecords();
                                    if (empty($selectedRecords)) return [];

                                    $tickets = $selectedRecords->filter(
                                        fn($ticket) =>
                                        $ticket->ticket_type_code !== 'VOID' &&
                                            $ticket->invoices()->where('type', 'sale')->count() == 0
                                    );

                                    return $tickets->map(function ($ticket) {
                                        $totalTaxes = ($ticket->cost_tax_amount ?? 0) + ($ticket->extra_tax_amount ?? 0);
                                        return [
                                            'ticket_number_core' => $ticket->ticket_number_core,
                                            'airline_name' => $ticket->airline_name,
                                            'total_taxes' => number_format($totalTaxes, 2),
                                            'sale_total_amount' => number_format($ticket->sale_total_amount, 2),
                                        ];
                                    })->toArray();
                                })
                                ->reorderable(false)
                                ->addable(false)
                                ->deletable(false)
                                ->columnSpanFull(),

                            Textarea::make('notes')
                                ->label('ملاحظات')
                                ->columnSpanFull()
                                ->hidden(fn(Get $get) => empty($get('tickets'))),
                        ])
                        ->action(function ($records, array $data) {
                            $records = $records->reject(fn($t) => $t->ticket_type_code == 'VOID');
                            if ($records->isEmpty()) {
                                Notification::make()->title('كل التذاكر المحددة عليها فواتير بالفعل')->danger()->send();
                                return;
                            }

                            $totalTaxes = $records->sum(fn($t) => ($t->cost_tax_amount ?? 0) + ($t->extra_tax_amount ?? 0));
                            $totalAmount = $records->sum('sale_total_amount');
                            $lastInvoiceId = Invoice::max('id') ?? 0;
                            $invoiceNumber = 'INV-' . now()->format('Y') . '-' . str_pad($lastInvoiceId + 1, 5, '0', STR_PAD_LEFT);

                            $typeMap = [
                                'client' => Client::class,
                                'branch' => Branch::class,
                                'franchise' => Franchise::class,
                                'supplier' => Supplier::class,
                            ];

                            $invoiceableType = $typeMap[$this->entityType] ?? null;

                            $invoice = Invoice::create([
                                'type' => 'sale',
                                'is_drafted' => false,
                                'total_taxes' => $totalTaxes,
                                'total_amount' => $totalAmount,
                                'invoice_number' => $invoiceNumber,
                                'notes' => $data['notes'] ?? null,
                                'invoiceable_type' => $invoiceableType,
                                'invoiceable_id' => $this->entityId,
                            ]);

                            foreach ($records as $ticket) {
                                $invoice->tickets()->attach($ticket->id);
                            }

                            Ticket::whereIn('id', $records->pluck('id'))->update(['is_invoiced' => true]);
                            Notification::make()->title('تم إنشاء الفاتورة رقم ' . $invoiceNumber)->success()->send();
                        })
                        ->requiresConfirmation()
                        ->modalWidth('4xl')
                        ->modalHeading('إضافة فاتورة')
                        ->modalSubmitActionLabel('إنشاء الفاتورة')
                        ->modalSubmitAction(function ($action) {
                            // التحقق من وجود تذاكر مؤهلة
                            $selectedRecords = $this->getSelectedTableRecords();

                            $hasEligibleTickets = false;
                            foreach ($selectedRecords as $ticket) {
                                if ($ticket->ticket_type_code !== 'VOID' && $ticket->invoices()->where('type', 'sale')->count() == 0) {
                                    $hasEligibleTickets = true;
                                    break;
                                }
                            }

                            return $action->disabled(!$hasEligibleTickets);
                        })
                        ->color('success')
                        ->deselectRecordsAfterCompletion()
                        ->accessSelectedRecords(),

                    // فاتورة استرجاع
                    Action::make('addRefundInvoice')
                        ->label('اضافة فاتورة استرجاع')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->visible(fn() => $this->activeTab === 'without_invoice' && $this->entityType !== 'supplier')
                        ->schema([
                            Repeater::make('tickets')
                                ->label('تفاصيل التذاكر')
                                ->schema([
                                    Grid::make(2)->schema([
                                        TextInput::make('ticket_number_core')->label('رقم التذكرة')->disabled()->dehydrated(),
                                        TextInput::make('airline_name')->label('الخطوط الجوية')->disabled()->dehydrated(),
                                        TextInput::make('total_taxes')->label('إجمالي الضرائب')->disabled()->dehydrated(),
                                        TextInput::make('sale_total_amount')->label('سعر البيع')->disabled()->dehydrated(),
                                    ]),
                                ])
                                ->default(function () {
                                    $selectedRecords = $this->getSelectedTableRecords();
                                    if (empty($selectedRecords)) return [];

                                    $tickets = $selectedRecords->filter(
                                        fn($ticket) =>
                                        $ticket->ticket_type_code === 'VOID' &&
                                            $ticket->invoices()->where('type', 'refund')->count() == 0
                                    );

                                    return $tickets->map(function ($ticket) {
                                        $totalTaxes = ($ticket->cost_tax_amount ?? 0) + ($ticket->extra_tax_amount ?? 0);
                                        return [
                                            'ticket_number_core' => $ticket->ticket_number_core,
                                            'airline_name' => $ticket->airline_name,
                                            'total_taxes' => number_format($totalTaxes, 2),
                                            'sale_total_amount' => number_format($ticket->sale_total_amount, 2),
                                        ];
                                    })->toArray();
                                })
                                ->reorderable(false)
                                ->addable(false)
                                ->deletable(false)
                                ->columnSpanFull(),
                            Textarea::make('notes')
                                ->label('ملاحظات')
                                ->columnSpanFull()
                                ->hidden(fn(Get $get) => empty($get('tickets'))),
                        ])
                        ->action(function ($records, array $data) {
                            $records = $records->reject(fn($t) => $t->ticket_type_code !== 'VOID');
                            if ($records->isEmpty()) {
                                Notification::make()->title('كل التذاكر المحددة عليها فواتير بالفعل او تذاكر غير مسترجعة')->danger()->send();
                                return;
                            }

                            $totalTaxes = $records->sum(fn($t) => ($t->cost_tax_amount ?? 0) + ($t->extra_tax_amount ?? 0));
                            $totalAmount = $records->sum('sale_total_amount');
                            $totalProfit = $records->sum('profit_amount');
                            $totalAmount -= $totalProfit;

                            $lastInvoiceId = Invoice::max('id') ?? 0;
                            $invoiceNumber = 'INV-' . now()->format('Y') . '-' . str_pad($lastInvoiceId + 1, 5, '0', STR_PAD_LEFT);

                            $typeMap = [
                                'client' => Client::class,
                                'branch' => Branch::class,
                                'franchise' => Franchise::class,
                                'supplier' => Supplier::class,
                            ];

                            $invoiceableType = $typeMap[$this->entityType] ?? null;

                            $invoice = Invoice::create([
                                'type' => 'refund',
                                'is_drafted' => false,
                                'total_taxes' => $totalTaxes,
                                'total_amount' => $totalAmount,
                                'invoice_number' => $invoiceNumber,
                                'notes' => $data['notes'] ?? null,
                                'invoiceable_type' => $invoiceableType,
                                'invoiceable_id' => $this->entityId,
                            ]);

                            foreach ($records as $ticket) {
                                $invoice->tickets()->attach($ticket->id);
                            }

                            Ticket::whereIn('id', $records->pluck('id'))->update(['is_refunded' => true]);
                            Notification::make()->title('فواتير الاسترجاع')->body('تم إنشاء فواتير الاسترجاع بنجاح')->success()->send();
                        })
                        ->requiresConfirmation()
                        ->modalWidth('4xl')
                        ->modalHeading('إنشاء فاتورة استرجاع')
                        ->modalSubmitActionLabel('إنشاء فواتير الاسترجاع')
                        ->modalSubmitAction(function ($action) {
                            // التحقق من وجود تذاكر مؤهلة
                            $selectedRecords = $this->getSelectedTableRecords();

                            $hasEligibleTickets = false;
                            foreach ($selectedRecords as $ticket) {
                                if ($ticket->ticket_type_code === 'VOID' && $ticket->invoices()->where('type', 'refund')->count() == 0) {
                                    $hasEligibleTickets = true;
                                    break;
                                }
                            }

                            return $action->disabled(!$hasEligibleTickets);
                        })
                        ->color('danger')
                        ->deselectRecordsAfterCompletion()
                        ->accessSelectedRecords(),

                    // فاتورة شراء
                    Action::make('addPurchaseInvoice')
                        ->label('اضافة فاتورة شراء')
                        ->icon('heroicon-o-document-text')
                        ->visible(fn() => $this->activeTab === 'without_invoice' && $this->entityType === 'supplier')
                        ->schema([
                            Repeater::make('tickets')
                                ->label('تفاصيل التذاكر')
                                ->schema([
                                    Grid::make(2)->schema([
                                        TextInput::make('ticket_number_core')->label('رقم التذكرة')->disabled()->dehydrated(),
                                        TextInput::make('airline_name')->label('الخطوط الجوية')->disabled()->dehydrated(),
                                        TextInput::make('total_taxes')->label('إجمالي الضرائب')->disabled()->dehydrated(),
                                        TextInput::make('sale_total_amount')->label('سعر البيع')->disabled()->dehydrated(),
                                    ]),
                                ])
                                ->default(function () {
                                    $selectedRecords = $this->getSelectedTableRecords();
                                    if (empty($selectedRecords)) return [];

                                    $tickets = $selectedRecords->filter(
                                        fn($ticket) =>
                                        $ticket->ticket_type_code !== 'VOID' &&
                                            $ticket->invoices()->where('type', 'purchase')->count() == 0
                                    );

                                    return $tickets->map(function ($ticket) {
                                        $totalTaxes = ($ticket->cost_tax_amount ?? 0) + ($ticket->extra_tax_amount ?? 0);
                                        return [
                                            'ticket_number_core' => $ticket->ticket_number_core,
                                            'airline_name' => $ticket->airline_name,
                                            'total_taxes' => number_format($totalTaxes, 2),
                                            'sale_total_amount' => number_format($ticket->sale_total_amount, 2),
                                        ];
                                    })->toArray();
                                })
                                ->reorderable(false)
                                ->addable(false)
                                ->deletable(false)
                                ->columnSpanFull(),
                            Textarea::make('notes')
                                ->label('ملاحظات')
                                ->columnSpanFull()
                                ->hidden(fn(Get $get) => empty($get('tickets'))),
                        ])
                        ->action(function ($records, array $data) {
                            $records = $records->reject(fn($t) => $t->ticket_type_code == 'VOID');
                            if ($records->isEmpty()) {
                                Notification::make()->title('كل التذاكر المحددة عليها فواتير بالفعل')->danger()->send();
                                return;
                            }

                            $totalTaxes = $records->sum(fn($t) => ($t->cost_tax_amount ?? 0) + ($t->extra_tax_amount ?? 0));
                            $totalAmount = $records->sum('sale_total_amount');
                            $lastInvoiceId = Invoice::max('id') ?? 0;
                            $invoiceNumber = 'INV-' . now()->format('Y') . '-' . str_pad($lastInvoiceId + 1, 5, '0', STR_PAD_LEFT);

                            $invoice = Invoice::create([
                                'type' => 'purchase',
                                'is_drafted' => false,
                                'total_taxes' => $totalTaxes,
                                'total_amount' => $totalAmount,
                                'invoice_number' => $invoiceNumber,
                                'notes' => $data['notes'] ?? null,
                                'invoiceable_type' => Supplier::class,
                                'invoiceable_id' => $this->entityId,
                            ]);

                            foreach ($records as $ticket) {
                                $invoice->tickets()->attach($ticket->id);
                            }

                            Ticket::whereIn('id', $records->pluck('id'))->update(['is_purchased' => true]);
                            Notification::make()->title('تم إنشاء الفاتورة رقم ' . $invoiceNumber)->success()->send();
                        })
                        ->requiresConfirmation()
                        ->modalWidth('4xl')
                        ->modalHeading('إضافة فاتورة')
                        ->modalSubmitActionLabel('إنشاء الفاتورة')
                        ->modalSubmitAction(function ($action) {
                            // التحقق من وجود تذاكر مؤهلة
                            $selectedRecords = $this->getSelectedTableRecords();

                            $hasEligibleTickets = false;
                            foreach ($selectedRecords as $ticket) {
                                if ($ticket->ticket_type_code !== 'VOID' && $ticket->invoices()->where('type', 'purchase')->count() == 0) {
                                    $hasEligibleTickets = true;
                                    break;
                                }
                            }

                            return $action->disabled(!$hasEligibleTickets);
                        })
                        ->color('primary')
                        ->deselectRecordsAfterCompletion()
                        ->accessSelectedRecords(),

                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading($this->getEmptyStateHeading())
            ->emptyStateDescription($this->getEmptyStateDescription());
    }

    protected function applyTabFilter(Builder $query): Builder
    {
        if (!$this->entityType) {
            return $query;
        }

        return match ($this->activeTab) {
            'all' => $query,
            'without_invoice' => $query->whereDoesntHave('invoices'),
            'sale_invoices' => $query->whereHas('invoices', function ($q) {
                $q->where('type', 'sale');
            }),
            'purchase_invoices' => $query->whereHas('invoices', function ($q) {
                $q->where('type', 'purchase');
            }),
            'return_invoices' => $query->whereHas('invoices', function ($q) {
                $q->where('type', 'refund');
            }),
            default => $query,
        };
    }

    protected function getTabs(): array
    {
        if (!$this->entityType) {
            return [];
        }

        $tabs = [];

        $tabs['all'] = Tab::make('الكل')
        ->badge(fn() => Ticket::query()->where("{$this->entityType}_id", $this->entityId)->count());

        $tabs['without_invoice'] = Tab::make('تذاكر بدون فاتورة')
            ->badge(function () {
                $count = Ticket::query()
                    ->where("{$this->entityType}_id", $this->entityId)
                    ->whereDoesntHave('invoices')
                    ->count();
                return $count ?: null;
            });

        if (in_array($this->entityType, ['client', 'branch', 'franchise'])) {
            $tabs['sale_invoices'] = Tab::make('تذاكر بفواتير بيع')
                ->badge(function () {
                    $count = Ticket::query()
                        ->where("{$this->entityType}_id", $this->entityId)
                        ->whereHas('invoices', function ($q) {
                            $q->where('type', 'sale');
                        })
                        ->count();
                    return $count ?: null;
                });

            $tabs['return_invoices'] = Tab::make('تذاكر بفواتير استرجاع')
                ->badge(function () {
                    $count = Ticket::query()
                        ->where("{$this->entityType}_id", $this->entityId)
                        ->whereHas('invoices', function ($q) {
                            $q->where('type', 'refund');
                        })
                        ->count();
                    return $count ?: null;
                });
        }

        if ($this->entityType === 'supplier') {
            $tabs['purchase_invoices'] = Tab::make('تذاكر بفواتير شراء')
                ->badge(function () {
                    $count = Ticket::query()
                        ->where("{$this->entityType}_id", $this->entityId)
                        ->whereHas('invoices', function ($q) {
                            $q->where('type', 'purchase');
                        })
                        ->count();
                    return $count ?: null;
                });
        }

        return $tabs;
    }

    protected function getEmptyStateHeading(): string
    {
        $tabTitles = [
            'without_invoice' => 'تذاكر بدون فاتورة',
            'sale_invoices' => 'تذاكر بفواتير بيع',
            'purchase_invoices' => 'تذاكر بفواتير شراء',
            'return_invoices' => 'تذاكر بفواتير استرجاع',
        ];

        $tabTitle = $tabTitles[$this->activeTab] ?? 'تذاكر';

        if ($this->entityType && $this->entityId) {
            return "لا توجد {$tabTitle}";
        }
        return 'لا توجد تذاكر في النظام';
    }

    protected function getEmptyStateDescription(): string
    {
        $tabTitles = [
            'without_invoice' => 'تذاكر بدون فاتورة',
            'sale_invoices' => 'تذاكر بفواتير بيع',
            'purchase_invoices' => 'تذاكر بفواتير شراء',
            'return_invoices' => 'تذاكر بفواتير استرجاع',
        ];

        $tabTitle = $tabTitles[$this->activeTab] ?? 'تذاكر';

        if ($this->entityType && $this->entityId) {
            $entityName = $this->entity?->name ?? $this->entity?->company_name ?? 'هذا الكيان';
            return "لم يتم إضافة أي {$tabTitle} لـ {$entityName} حتى الآن.";
        }
        return 'لم يتم إضافة أي تذاكر في النظام حتى الآن.';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }
}
