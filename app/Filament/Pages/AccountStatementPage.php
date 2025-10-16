<?php

namespace App\Filament\Pages;

use App\Filament\Exports\AccountStatementExporter;
use App\Models\AccountStatement;
use App\Models\Client;
use App\Models\Supplier;
use App\Models\Branch;
use App\Models\Franchise;
use App\Models\Invoice;
use App\Models\Payment;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportBulkAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Illuminate\Support\Facades\Auth;

class AccountStatementPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected $queryString = [
        'tableSortColumn',
        'tableSortDirection',
        'tableFilters',
    ];

    protected string $view = 'filament.pages.account-statement-page';

    public ?Model $model = null;

    public function getTitle(): string
    {
        return __('dashboard.sidebar.account_statement');
    }

    public static function getNavigationLabel(): string
    {
        return __('dashboard.sidebar.account_statement');
    }

    public static function canAccess(): bool
    {
        return Auth::user()->can('account_statement.view');
    }

     // إضافة Header Actions
     protected function getHeaderActions(): array
     {
         return [
             Action::make('addPayment')
                 ->label('تسديد / إيداع')
                 ->icon('heroicon-o-banknotes')
                 ->color('primary')
                 ->size('lg')
                 ->schema([
                     Grid::make(2)
                         ->schema([
                             // النوع (read only)
                             TextInput::make('statementable_type_display')
                                 ->label('نوع الجهة')
                                 ->default(function () {
                                     $filters = $this->tableFilters['account_filter'] ?? [];
                                     $type = $filters['statementable_type'] ?? null;
                                     
                                     if ($type) {
                                         return match ($type) {
                                             Client::class => 'عميل',
                                             Supplier::class => 'مورد',
                                             Branch::class => 'فرع',
                                             Franchise::class => 'فرانشايز',
                                             default => $type
                                         };
                                     }
                                     return 'لم يتم اختيار نوع';
                                 })
                                 ->disabled()
                                 ->dehydrated()
                                 ->required(),
 
                             // الجهة (read only)
                             TextInput::make('statementable_name')
                                 ->label('الجهة')
                                 ->default(function () {
                                     $filters = $this->tableFilters['account_filter'] ?? [];
                                     $type = $filters['statementable_type'] ?? null;
                                     $id = $filters['statementable_id'] ?? null;
                                     
                                     if ($type && $id) {
                                         return self::getStatementableName($type, $id);
                                     }
                                     return 'لم يتم اختيار جهة';
                                 })
                                 ->disabled()
                                 ->dehydrated()
                                 ->required(),
 
                             // طريقة الدفع
                             Select::make('payment_method')
                                 ->label('طريقة الدفع')
                                 ->options([
                                     'cash' => 'نقدي',
                                     'bank_transfer' => 'تحويل بنكي',
                                     'check' => 'شيك',
                                     'credit_card' => 'بطاقة ائتمان',
                                 ])
                                 ->required()
                                 ->native(false),
 
                             // المبلغ
                             TextInput::make('amount')
                                 ->label('المبلغ')
                                 ->numeric()
                                 ->required()
                                 ->minValue(0.01)
                                 ->suffix('SAR'),
 
                             // تاريخ الدفع
                             DatePicker::make('payment_date')
                                 ->label('تاريخ الدفع')
                                 ->default(now())
                                 ->required(),
 
                             // الحساب / الخزينة
                             Select::make('account')
                                 ->label('الحساب / الخزينة')
                                 ->options([
                                     'main_cash' => 'الخزينة الرئيسية',
                                     'bank_account_1' => 'الحساب البنكي 1',
                                     'bank_account_2' => 'الحساب البنكي 2',
                                     'petty_cash' => 'الصندوق الصغير',
                                 ])
                                 ->required()
                                 ->native(false),
 
                         ]),
 
                     // الملاحظات
                     Textarea::make('notes')
                         ->label('ملاحظات')
                         ->columnSpanFull(),
                 ])
                 ->action(function (array $data) {
                     $filters = $this->tableFilters['account_filter'] ?? [];
                     $statementableType = $filters['statementable_type'] ?? null;
                     $statementableId = $filters['statementable_id'] ?? null;
 
                     if (!$statementableType || !$statementableId) {
                         Notification::make()
                             ->title('خطأ في الإيداع')
                             ->body('يجب اختيار نوع الجهة والجهة من الفلتر أولاً')
                             ->danger()
                             ->send();
                         return;
                     }
 
                     try {
                         // إنشاء سجل الدفع
                         $payment = Payment::create([
                             'paymentable_type' => $statementableType,
                             'paymentable_id' => $statementableId,
                             'payment_method' => $data['payment_method'],
                             'amount' => $data['amount'],
                             'payment_date' => $data['payment_date'],
                             'account' => $data['account'],
                             'notes' => $data['notes'] ?? null,
                         ]);
 
                         // إنشاء سجل في كشف الحساب
                         AccountStatement::create([
                             'statementable_type' => $statementableType,
                             'statementable_id' => $statementableId,
                             'date' => $data['payment_date'],
                             'doc_no' => 'PAY-' . $payment->id,
                             'debit' =>  $statementableType == Supplier::class ? $data['amount'] : 0, // الإيداع يكون دائن
                             'credit' => $statementableType == Supplier::class ?  0 : $data['amount'],
                             'balance' => 0, // سيتم حسابه تلقائياً في الـ boot
                         ]);
 
                         Notification::make()
                             ->title('تمت عملية الإيداع بنجاح')
                             ->body('تم تسجيل الدفع بمبلغ ' . number_format($data['amount'], 2) . ' SAR للجهة: ' . self::getStatementableName($statementableType, $statementableId))
                             ->success()
                             ->send();
 
                     } catch (\Exception $e) {
                         Notification::make()
                             ->title('خطأ في عملية الإيداع')
                             ->body($e->getMessage())
                             ->danger()
                             ->send();
                     }
                 })
                 ->requiresConfirmation()
                 ->modalHeading('تسديد / إيداع')
                 ->modalSubmitActionLabel('تأكيد الإيداع')
                 ->modalWidth('2xl')
                 ->visible(fn() => !empty($this->tableFilters['account_filter']['statementable_type']) && !empty($this->tableFilters['account_filter']['statementable_id'])),
         ];
     }

    private static function getStatementableName($type, $id): string
    {
        return match ($type) {
            Client::class => Client::find($id)?->name ?? 'غير معروف',
            Supplier::class => Supplier::find($id)?->name ?? 'غير معروف',
            Branch::class => Branch::find($id)?->name ?? 'غير معروف',
            Franchise::class => Franchise::find($id)?->name ?? 'غير معروف',
            default => 'غير معروف',
        };
    }

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::DocumentText;

    public static function table(Table $table): Table
    {
        return $table
            ->query(AccountStatement::query())
            ->columns([
                TextColumn::make('date')
                    ->label('التاريخ')
                    ->date()
                    ->sortable(),

                TextColumn::make('reservation_number/pnr')
                    ->label('رقم الحجز/Pnr')
                    ->getStateUsing(fn($record) => $record->reservation?->reservation_number ?? $record->ticket?->pnr)
                    ->searchable(),

                TextColumn::make('doc_no')
                    ->label('رقم المستند')
                    ->getStateUsing(fn($record) => $record->invoices->pluck('invoice_number')->first())
                    ->searchable(),

                TextColumn::make('ticket.ticket_number_full')
                    ->label('التذكرة'),

                TextColumn::make('passengers')
                    ->label('المسافر')
                    ->getStateUsing(fn($record) => $record->passengers->pluck('first_name')->implode(', ')),

                TextColumn::make('sector')
                    ->label('القطاع'),

                TextColumn::make('debit')
                    ->label('مدين')
                    ->numeric()
                    ->sortable()
                    ->color('danger')
                    ->summarize([
                        Sum::make()
                            ->label('إجمالي المدين')
                            ->numeric()
                    ]),

                TextColumn::make('credit')
                    ->label('دائن')
                    ->numeric()
                    ->sortable()
                    ->color('success')
                    ->summarize([
                        Sum::make()
                            ->label('إجمالي الدائن')
                            ->numeric()
                    ]),

                TextColumn::make('balance')
                    ->label('الرصيد')
                    ->numeric()
                    ->sortable()
                    ->color(fn($state) => $state < 0 ? 'danger' : 'success')
                    ->summarize([
                        Summarizer::make()
                            ->label('الرصيد الإجمالي')
                            ->using(function ($query) {
                                $totalDebit  = $query->sum('debit');
                                $totalCredit = $query->sum('credit');
                                return $totalDebit - $totalCredit;
                            })
                            ->numeric()
                    ]),

                TextColumn::make('statementable_type')
                    ->label('النوع')
                    ->formatStateUsing(fn($state) => match ($state) {
                        Client::class => 'عميل',
                        Supplier::class => 'مورد',
                        Branch::class => 'فرع',
                        Franchise::class => 'فرانشايز',
                        default => $state
                    }),

                TextColumn::make('statementable.name')
                    ->label('الجهة'),

                // عمود جديد لعرض حالة الفاتورة
                TextColumn::make('invoice_status')
                    ->label('حالة الفاتورة')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        if ($record->type === 'refund') {
                            return 'مسترجعة';
                        }
                        
                        if ($record->type === 'sale') {
                            return $record->invoices()->exists() ? 'مفوترة' : 'غير مفوترة';
                        }
                        
                        return null;
                    })
                    ->colors([
                        'success' => 'مفوترة',
                        'danger' => 'مسترجعة',
                        'warning' => 'غير مفوترة',
                    ]),

                // نوع الفاتورة (حجز أم تذكرة)
                TextColumn::make('invoice_type')
                    ->label('نوع الفاتورة')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        if ($record->reservation_id) {
                            return 'فاتورة حجز';
                        }
                        if ($record->originalInvoices()->exists() || $record->refundInvoices()->exists()) {
                            return 'فاتورة تذكرة';
                        }
                        return '-';
                    })
                    ->colors([
                        'primary' => 'فاتورة حجز',
                        'info' => 'فاتورة تذكرة',
                        'gray' => '-',
                    ]),
            ])
            ->filters([
                // فلتر التاريخ
                Filter::make('date_filter')
                    ->schema([
                        Select::make('date_range')
                            ->label('الفترة الزمنية')
                            ->options([
                                'current_month' => 'الشهر الحالي',
                                'last_month' => 'الشهر السابق',
                                'custom' => 'مخصص',
                            ])
                            ->default('current_month')
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state !== 'custom') {
                                    $set('start_date', null);
                                    $set('end_date', null);
                                }
                            }),

                        DatePicker::make('start_date')
                            ->label('من تاريخ')
                            ->visible(fn(callable $get) => $get('date_range') === 'custom'),

                        DatePicker::make('end_date')
                            ->label('إلى تاريخ')
                            ->visible(fn(callable $get) => $get('date_range') === 'custom')
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                if ($get('start_date') && !$state) {
                                    $set('end_date', $get('start_date'));
                                }
                            }),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['date_range'] ?? null, function (Builder $query, $range) use ($data) {
                                $now = Carbon::now();

                                return match ($range) {
                                    'current_month' => $query->whereBetween('date', [
                                        $now->startOfMonth()->format('Y-m-d'),
                                        $now->endOfMonth()->format('Y-m-d')
                                    ]),
                                    'last_month' => $query->whereBetween('date', [
                                        $now->subMonth()->startOfMonth()->format('Y-m-d'),
                                        $now->subMonth()->endOfMonth()->format('Y-m-d')
                                    ]),
                                    'custom' => $query
                                        ->when(
                                            $data['start_date'] ?? null,
                                            fn(Builder $query, $date): Builder => $query->whereDate('date', '>=', $date),
                                        )
                                        ->when(
                                            $data['end_date'] ?? null,
                                            fn(Builder $query, $date): Builder => $query->whereDate('date', '<=', $date),
                                        ),
                                    default => $query,
                                };
                            });
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['date_range'] ?? null) {
                            $rangeLabel = match ($data['date_range']) {
                                'current_month' => 'الشهر الحالي',
                                'last_month' => 'الشهر السابق',
                                'custom' => 'مخصص',
                                default => $data['date_range']
                            };

                            $indicatorText = 'الفترة: ' . $rangeLabel;

                            if ($data['date_range'] === 'custom') {
                                if ($data['start_date'] ?? null) {
                                    $indicatorText .= ' من ' . Carbon::parse($data['start_date'])->format('Y-m-d');
                                }
                                if ($data['end_date'] ?? null) {
                                    $indicatorText .= ' إلى ' . Carbon::parse($data['end_date'])->format('Y-m-d');
                                }
                            }

                            $indicators[] = \Filament\Tables\Filters\Indicator::make($indicatorText)
                                ->removeField('date_range')
                                ->removeField('start_date')
                                ->removeField('end_date');
                        }

                        return $indicators;
                    }),

                // فلتر الجهة
                Filter::make('account_filter')
                    ->form([
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
                            ->disabled(fn(callable $get) => !$get('statementable_type'))
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['statementable_type'] ?? null,
                                fn(Builder $query, $type): Builder => $query->where('statementable_type', $type)
                            )
                            ->when(
                                $data['statementable_id'] ?? null,
                                fn(Builder $query, $id): Builder => $query->where('statementable_id', $id)
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['statementable_type'] ?? null) {
                            $typeLabel = match ($data['statementable_type']) {
                                Client::class => 'عميل',
                                Supplier::class => 'مورد',
                                Branch::class => 'فرع',
                                Franchise::class => 'فرانشايز',
                                default => $data['statementable_type']
                            };
                            $indicators[] = \Filament\Tables\Filters\Indicator::make('النوع: ' . $typeLabel)
                                ->removeField('statementable_type');
                        }

                        if ($data['statementable_id'] ?? null) {
                            $type = $data['statementable_type'] ?? null;
                            $name = '';

                            if ($type) {
                                $name = match ($type) {
                                    Client::class    => Client::find($data['statementable_id'])?->name ?? $data['statementable_id'],
                                    Supplier::class  => Supplier::find($data['statementable_id'])?->name ?? $data['statementable_id'],
                                    Branch::class    => Branch::find($data['statementable_id'])?->name ?? $data['statementable_id'],
                                    Franchise::class => Franchise::find($data['statementable_id'])?->name ?? $data['statementable_id'],
                                    default          => $data['statementable_id'],
                                };
                            }

                            $indicators[] = \Filament\Tables\Filters\Indicator::make('الجهة: ' . $name)
                                ->removeField('statementable_id');
                        }

                        return $indicators;
                    })
            ])
            ->recordActions([
                // عرض الفاتورة الأصلية
                Action::make('showInvoice')
                    ->label('عرض الفاتورة')
                    ->icon('heroicon-o-eye')
                    ->color('success')
                    ->visible(function ($record) {
                        if($record->type == 'sale'){
                        if ($record->reservation_id) {
                            return Invoice::where('reservation_id', $record->reservation_id)
                                ->where('type', '!=', 'refund')
                                ->exists();
                        }
                        return $record->originalInvoices()->exists();
                    
                    }
                    })
                    ->url(function ($record) {
                        if ($record->reservation_id) {
                            $id = Invoice::where('reservation_id', $record->reservation_id)
                                ->where('type', '!=', 'refund')
                                ->value('id');
                            return route('reservations.invoices.print', $id);
                        }
                        return route('invoices.print', $record->originalInvoices()->first()->id);
                    })
                    ->openUrlInNewTab(),

                // عرض فاتورة الاسترجاع
                Action::make('showRefundInvoice')
                    ->label('عرض فاتورة الاسترجاع')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->visible(function ($record) {
                        if ($record->type == 'refund') {
                        if ($record->reservation_id) {
                            return Invoice::where('reservation_id', $record->reservation_id)
                                ->where('type', 'refund')
                                ->exists();
                        }
                        return $record->refundInvoices()->exists();

                    }
                    })
                    ->url(function ($record) {
                        if ($record->reservation_id) {
                            $id = Invoice::where('reservation_id', $record->reservation_id)
                                ->where('type', 'refund')
                                ->value('id');
                            return route('reservations.invoices.print', $id);
                        }
                        return route('invoices.print', $record->refundInvoices()->first()->id);
                    })
                    ->openUrlInNewTab(),

                // // إضافة فاتورة أصلية
                // Action::make('addInvoice')
                //     ->label('إضافة فاتورة')
                //     ->icon('heroicon-o-plus')
                //     ->color('success')
                //     ->visible(fn($record) => !$record->invoices()->exists())
                //     ->schema([
                //         Grid::make(2)
                //             ->schema([
                //                 TextInput::make('ticket_number_core')
                //                     ->label('رقم التذكرة')
                //                     ->default(fn($record) => $record->ticket->ticket_number_core ?? '')
                //                     ->disabled()
                //                     ->dehydrated(),

                //                 TextInput::make('airline_name')
                //                     ->label('الخطوط الجوية')
                //                     ->default(fn($record) => $record->ticket->airline_name ?? '')
                //                     ->disabled()
                //                     ->dehydrated(),

                //                 TextInput::make('total_taxes')
                //                     ->label('إجمالي الضرائب')
                //                     ->default(function ($record) {
                //                         $ticket = $record->ticket;
                //                         if (!$ticket) return '0.00';

                //                         $totalTaxes = ($ticket->cost_tax_amount ?? 0) + ($ticket->extra_tax_amount ?? 0);
                //                         return number_format($totalTaxes, 2);
                //                     })
                //                     ->disabled()
                //                     ->dehydrated(),

                //                 TextInput::make('sale_total_amount')
                //                     ->label('سعر البيع')
                //                     ->default(fn($record) => number_format($record->ticket->sale_total_amount ?? 0, 2))
                //                     ->disabled()
                //                     ->dehydrated(),
                //             ]),

                //         Select::make('statementable_type')
                //             ->label('نوع الجهة')
                //             ->options([
                //                 Client::class    => 'عميل',
                //                 Supplier::class  => 'مورد',
                //                 Branch::class    => 'فرع',
                //                 Franchise::class => 'فرانشايز',
                //             ])
                //             ->default(fn($record) => $record->statementable_type)
                //             ->native(false)
                //             ->required()
                //             ->disabled(),

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
                //             ->default(fn($record) => $record->statementable_id)
                //             ->native(false)
                //             ->required()
                //             ->disabled(),

                //         Textarea::make('notes')
                //             ->label('ملاحظات')
                //             ->columnSpanFull(),
                //     ])
                //     ->action(function ($record, array $data) {
                //         if ($record->invoices()->exists()) {
                //             Notification::make()
                //                 ->title('هذه التذكرة عليها فاتورة بالفعل')
                //                 ->danger()
                //                 ->send();
                //             return;
                //         }

                //         $ticket = $record->ticket;
                //         if (!$ticket) {
                //             Notification::make()
                //                 ->title('لا توجد تذكرة مرتبطة')
                //                 ->danger()
                //                 ->send();
                //             return;
                //         }

                //         $totalTaxes = ($ticket->cost_tax_amount ?? 0) + ($ticket->extra_tax_amount ?? 0);
                //         $totalAmount = $ticket->sale_total_amount ?? 0;

                //         $lastInvoiceId = Invoice::max('id') ?? 0;
                //         $invoiceNumber = 'INV-' . now()->format('Y') . '-' . str_pad($lastInvoiceId + 1, 5, '0', STR_PAD_LEFT);

                //         $invoice = Invoice::create([
                //             'type' => 'sale',
                //             'is_drafted' => false,
                //             'total_taxes' => $totalTaxes,
                //             'total_amount' => $totalAmount,
                //             'invoice_number' => $invoiceNumber,
                //             'notes' => $data['notes'] ?? null,
                //             'invoiceable_type' => $record->statementable_type,
                //             'invoiceable_id' => $record->statementable_id,
                //         ]);

                //         $invoice->tickets()->attach($ticket->id);
                //         $ticket->update(['is_invoiced' => true]);

                //         Notification::make()
                //             ->title('تم إنشاء الفاتورة رقم ' . $invoiceNumber)
                //             ->success()
                //             ->send();
                //     })
                //     ->requiresConfirmation()
                //     ->modalWidth('4xl')
                //     ->modalHeading('إضافة فاتورة')
                //     ->modalSubmitActionLabel('إنشاء الفاتورة'),

                // // إضافة فاتورة استرجاع
                // Action::make('addRefundInvoice')
                //     ->label('إضافة فاتورة استرجاع')
                //     ->icon('heroicon-o-arrow-uturn-left')
                //     ->color('danger')
                //     ->visible(fn($record) => $record->originalInvoices()->exists() && !$record->refundInvoices()->exists())
                //     ->schema([
                //         Grid::make(2)
                //             ->schema([
                //                 TextInput::make('ticket_number_core')
                //                     ->label('رقم التذكرة')
                //                     ->default(fn($record) => $record->ticket->ticket_number_core ?? '')
                //                     ->disabled()
                //                     ->dehydrated(),

                //                 TextInput::make('airline_name')
                //                     ->label('الخطوط الجوية')
                //                     ->default(fn($record) => $record->ticket->airline_name ?? '')
                //                     ->disabled()
                //                     ->dehydrated(),

                //                 TextInput::make('original_invoice')
                //                     ->label('الفاتورة الأصلية')
                //                     ->default(function ($record) {
                //                         $originalInvoice = $record->originalInvoices()->first();
                //                         return $originalInvoice ? $originalInvoice->invoice_number : 'لا توجد فاتورة';
                //                     })
                //                     ->disabled()
                //                     ->dehydrated(),

                //                 TextInput::make('sale_total_amount')
                //                     ->label('سعر البيع')
                //                     ->default(fn($record) => number_format($record->ticket->sale_total_amount, 2))
                //                     ->disabled()
                //                     ->dehydrated(),
                //             ]),

                //         Textarea::make('notes')
                //             ->label('ملاحظات الاسترجاع')
                //             ->columnSpanFull(),
                //     ])
                //     ->action(function ($record, array $data) {
                //         if ($record->refundInvoices()->exists()) {
                //             Notification::make()
                //                 ->title('هذه التذكرة عليها فاتورة استرجاع بالفعل')
                //                 ->danger()
                //                 ->send();
                //             return;
                //         }

                //         $originalInvoice = $record->originalInvoices()->first();
                //         if (!$originalInvoice) {
                //             Notification::make()
                //                 ->title('لا توجد فاتورة أصلية للاسترجاع')
                //                 ->danger()
                //                 ->send();
                //             return;
                //         }

                //         $ticket = $record->ticket;
                //         if (!$ticket) {
                //             Notification::make()
                //                 ->title('لا توجد تذكرة مرتبطة')
                //                 ->danger()
                //                 ->send();
                //             return;
                //         }

                //         $totalTaxes = ($ticket->cost_tax_amount ?? 0) + ($ticket->extra_tax_amount ?? 0);
                //         $totalAmount = $ticket->sale_total_amount ?? 0;

                //         $lastInvoiceId = Invoice::max('id') ?? 0;
                //         $refundInvoiceNumber = 'REF-' . now()->format('Y') . '-' . str_pad($lastInvoiceId + 1, 5, '0', STR_PAD_LEFT);

                //         $refundInvoice = Invoice::create([
                //             'type' => 'refund',
                //             'is_drafted' => false,
                //             'total_taxes' => $totalTaxes,
                //             'total_amount' => $totalAmount,
                //             'invoice_number' => $refundInvoiceNumber,
                //             'reference_num' => $originalInvoice->invoice_number,
                //             'notes' => $data['notes'] ?? null,
                //             'invoiceable_type' => $record->statementable_type,
                //             'invoiceable_id' => $record->statementable_id,
                //         ]);

                //         $refundInvoice->tickets()->attach($ticket->id);
                //         $ticket->update(['is_refunded' => true]);

                //         Notification::make()
                //             ->title('تم إنشاء فاتورة الاسترجاع رقم ' . $refundInvoiceNumber)
                //             ->success()
                //             ->send();
                //     })
                //     ->requiresConfirmation()
                //     ->modalWidth('4xl')
                //     ->modalHeading('إنشاء فاتورة استرجاع')
                //     ->modalSubmitActionLabel('إنشاء فاتورة الاسترجاع'),

                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ExportBulkAction::make()->exporter(AccountStatementExporter::class),
                    DeleteBulkAction::make(),

                    // إضافة فاتورة جماعية
                    Action::make('addInvoiceForStatements')
                        ->label('إضافة فاتورة')
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

                                    $tickets = collect();
                                    foreach ($selectedRecords as $record) {
                                        if ($record->ticket) {
                                            $totalTaxes = ($record->ticket->cost_tax_amount ?? 0) + ($record->ticket->extra_tax_amount ?? 0);

                                            $tickets->push([
                                                'ticket_number_core' => $record->ticket->ticket_number_core,
                                                'airline_name' => $record->ticket->airline_name,
                                                'total_taxes' => number_format($totalTaxes, 2),
                                                'sale_total_amount' => number_format($record->ticket->sale_total_amount, 2),
                                            ]);
                                        }
                                    }

                                    return $tickets->toArray();
                                })
                                ->reorderable(false)
                                ->addable(false)
                                ->deletable(false)
                                ->columnSpanFull(),

                            Textarea::make('notes')
                                ->label('ملاحظات')
                                ->columnSpanFull(),
                        ])
                        ->action(function ($records, array $data) {
                            $firstRecord = $records->first();
                            if (!$firstRecord) {
                                Notification::make()
                                    ->title('لم يتم اختيار أي كشوف حساب')
                                    ->danger()
                                    ->send();
                                return;
                            }

                            $tickets = $records->pluck('ticket')->filter();

                            if ($tickets->isEmpty()) {
                                Notification::make()
                                    ->title('لا توجد تذاكر مرتبطة بالكشوف المحددة')
                                    ->danger()
                                    ->send();
                                return;
                            }

                            $ticketsToInvoice = $tickets->where('is_invoiced', false);

                            if ($ticketsToInvoice->isEmpty()) {
                                Notification::make()
                                    ->title('جميع التذاكر المحددة عليها فواتير بالفعل')
                                    ->danger()
                                    ->send();
                                return;
                            }

                            $totalTaxes = $ticketsToInvoice->sum(fn($t) => ($t->cost_tax_amount ?? 0) + ($t->extra_tax_amount ?? 0));
                            $totalAmount = $ticketsToInvoice->sum('sale_total_amount');

                            $lastInvoiceId = Invoice::max('id') ?? 0;
                            $invoiceNumber = 'INV-' . now()->format('Y') . '-' . str_pad($lastInvoiceId + 1, 5, '0', STR_PAD_LEFT);

                            $invoice = Invoice::create([
                                'type' => 'sale',
                                'is_drafted' => false,
                                'total_taxes' => $totalTaxes,
                                'total_amount' => $totalAmount,
                                'invoice_number' => $invoiceNumber,
                                'notes' => $data['notes'] ?? null,
                                'invoiceable_type' => $firstRecord->statementable_type,
                                'invoiceable_id' => $firstRecord->statementable_id,
                            ]);

                            foreach ($ticketsToInvoice as $ticket) {
                                $invoice->tickets()->attach($ticket->id);
                                $ticket->update(['is_invoiced' => true]);
                            }

                            Notification::make()
                                ->title('تم إنشاء الفاتورة رقم ' . $invoiceNumber . ' لـ ' . $ticketsToInvoice->count() . ' تذكرة (تم تجاهل التذاكر المصدرة سابقاً)')
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->modalWidth('4xl')
                        ->modalHeading('إضافة فاتورة لكشوف الحساب المحددة')
                        ->modalSubmitActionLabel('إنشاء الفاتورة')
                        ->color('success')
                        ->bulk()
                        ->deselectRecordsAfterCompletion()
                        ->accessSelectedRecords(),

                    Action::make('addRefundInvoicesForStatements')
                        ->label('فاتورة استرجاع')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->color('danger')
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

                                            TextInput::make('original_invoice')
                                                ->label('الفاتورة الأصلية')
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

                                    $tickets = collect();
                                    foreach ($selectedRecords as $record) {
                                        if ($record->ticket) {
                                            $originalInvoice = $record->originalInvoices()->first();
                                            $tickets->push([
                                                'ticket_number_core' => $record->ticket->ticket_number_core,
                                                'airline_name' => $record->ticket->airline_name,
                                                'original_invoice' => $originalInvoice ? $originalInvoice->invoice_number : 'لا توجد فاتورة',
                                                'sale_total_amount' => number_format($record->ticket->sale_total_amount, 2),
                                                'has_original_invoice' => !is_null($originalInvoice),
                                                'has_refund_invoice' => $record->refundInvoices()->exists(),
                                                'original_invoice_id' => $originalInvoice ? $originalInvoice->id : null,
                                                'original_invoice_number' => $originalInvoice ? $originalInvoice->invoice_number : null,
                                            ]);
                                        }
                                    }

                                    return $tickets->toArray();
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
                            // تصفية السجلات التي لها فواتير أصلية ولا توجد لها فواتير استرجاع
                            $eligibleRecords = $records->filter(function ($record) {
                                return $record->originalInvoices()->exists() && !$record->refundInvoices()->exists();
                            });

                            if ($eligibleRecords->isEmpty()) {
                                Notification::make()
                                    ->title('لا توجد كشوف حساب مؤهلة للاسترجاع')
                                    ->body('جميع الكشوف المحددة إما ليس لها فواتير أصلية أو لها فواتير استرجاع مسبقة')
                                    ->danger()
                                    ->send();
                                return;
                            }

                            // تجميع التذاكر حسب الفاتورة الأصلية والجهة
                            $invoicesGroup = [];

                            foreach ($eligibleRecords as $record) {
                                $originalInvoice = $record->originalInvoices()->first();
                                $ticket = $record->ticket;

                                if ($originalInvoice && $ticket) {
                                    // مفتاح التجميع: الفاتورة الأصلية + الجهة
                                    $groupKey = $originalInvoice->id . '_' . $record->statementable_type . '_' . $record->statementable_id;

                                    if (!isset($invoicesGroup[$groupKey])) {
                                        $invoicesGroup[$groupKey] = [
                                            'original_invoice' => $originalInvoice,
                                            'statementable_type' => $record->statementable_type,
                                            'statementable_id' => $record->statementable_id,
                                            'tickets' => [],
                                            'total_taxes' => 0,
                                            'total_amount' => 0,
                                            'records_count' => 0,
                                        ];
                                    }

                                    $invoicesGroup[$groupKey]['tickets'][] = $ticket;
                                    $invoicesGroup[$groupKey]['total_taxes'] += ($ticket->cost_tax_amount ?? 0) + ($ticket->extra_tax_amount ?? 0);
                                    $invoicesGroup[$groupKey]['total_amount'] += $ticket->sale_total_amount ?? 0;
                                    $invoicesGroup[$groupKey]['records_count']++;
                                }
                            }

                            if (empty($invoicesGroup)) {
                                Notification::make()
                                    ->title('لا توجد تذاكر للاسترجاع')
                                    ->danger()
                                    ->send();
                                return;
                            }

                            $createdRefundInvoices = [];

                            // إنشاء فاتورة استرجاع لكل مجموعة
                            foreach ($invoicesGroup as $groupKey => $group) {
                                $lastInvoiceId = Invoice::max('id') ?? 0;
                                $refundInvoiceNumber = 'REF-' . now()->format('Y') . '-' . str_pad($lastInvoiceId + 1, 5, '0', STR_PAD_LEFT);

                                // إنشاء فاتورة الاسترجاع
                                $refundInvoice = Invoice::create([
                                    'type' => 'refund',
                                    'is_drafted' => false,
                                    'total_taxes' => $group['total_taxes'],
                                    'total_amount' => $group['total_amount'],
                                    'invoice_number' => $refundInvoiceNumber,
                                    'reference_num' => $group['original_invoice']->invoice_number, // رقم الفاتورة الأصلية
                                    'notes' => $data['notes'] ?? null,
                                    'invoiceable_type' => $group['statementable_type'],
                                    'invoiceable_id' => $group['statementable_id'],
                                ]);

                                // ربط التذاكر بفاتورة الاسترجاع
                                foreach ($group['tickets'] as $ticket) {
                                    $refundInvoice->tickets()->attach($ticket->id);
                                    $ticket->update(['is_refunded' => true]);

                                    AccountStatement::logTicket(
                                        $ticket,
                                        $ticket->accountStatement->first()->statementable_type,
                                        $ticket->accountStatement->first()->statementable_id,
                                        $ticket->accountStatement->first()->statementable_type == Supplier::class  ? false : true,
                                        'refund'
                                    );
                                }

                                $createdRefundInvoices[] = [
                                    'refund_number' => $refundInvoiceNumber,
                                    'original_number' => $group['original_invoice']->invoice_number,
                                    'tickets_count' => count($group['tickets']),
                                    'statementable' => self::getStatementableName($group['statementable_type'], $group['statementable_id'])
                                ];
                            }

                            $skippedRecords = $records->count() - $eligibleRecords->count();

                            // بناء رسالة النتيجة
                            $message = "تم إنشاء " . count($createdRefundInvoices) . " فاتورة استرجاع:\n\n";

                            foreach ($createdRefundInvoices as $invoice) {
                                $message .= "• {$invoice['refund_number']} (استرجاع للفاتورة {$invoice['original_number']} - {$invoice['tickets_count']} تذكرة - {$invoice['statementable']})\n";
                            }

                            if ($skippedRecords > 0) {
                                $message .= "\nتم تخطي {$skippedRecords} كشف حساب (لا توجد فواتير أصلية أو لها استرجاع مسبق)";
                            }

                            Notification::make()
                                ->title('فواتير الاسترجاع')
                                ->body($message)
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->modalWidth('4xl')
                        ->modalHeading('إنشاء فواتير استرجاع')
                        ->modalSubmitActionLabel('إنشاء فواتير الاسترجاع')
                        ->color('danger')
                        ->bulk()
                        ->deselectRecordsAfterCompletion()
                        ->accessSelectedRecords(),
                ]),
            ]);
    }
}
