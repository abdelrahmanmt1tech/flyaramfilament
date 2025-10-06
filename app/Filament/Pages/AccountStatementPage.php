<?php

namespace App\Filament\Pages;

use App\Filament\Exports\AccountStatementExporter;
use App\Models\AccountStatement;
use App\Models\Client;
use App\Models\Supplier;
use App\Models\Branch;
use App\Models\Franchise;
use App\Models\Invoice;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportBulkAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
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

                TextColumn::make('doc_no')
                    ->label('رقم المستند')
                    ->searchable(),

                TextColumn::make('ticket.ticket_number_core')
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
            ])
            ->filters([
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
                Action::make('showInvoice')
                    ->label('عرض الفاتورة')
                    ->icon('heroicon-o-eye')
                    ->color('success')
                    ->visible(fn($record) => $record->invoices()->exists()) // يظهر فقط لو فيه فاتورة
                    ->url(fn($record) => route('invoices.print', $record->invoices()->first()->id))
                    ->openUrlInNewTab(),
                //    ----------------------------------------------------------
                Action::make('addInvoice')
                    ->label('إضافة فاتورة')
                    ->icon('heroicon-o-plus')
                    ->color('success')
                    ->visible(fn($record) => !$record->invoices()->exists())
                    ->schema([
                        // عرض بيانات التذكرة
                        Grid::make(2)
                            ->schema([
                                TextInput::make('ticket_number_core')
                                    ->label('رقم التذكرة')
                                    ->default(fn($record) => $record->ticket->ticket_number_core ?? '')
                                    ->disabled()
                                    ->dehydrated(),

                                TextInput::make('airline_name')
                                    ->label('الخطوط الجوية')
                                    ->default(fn($record) => $record->ticket->airline_name ?? '')
                                    ->disabled()
                                    ->dehydrated(),

                                TextInput::make('total_taxes')
                                    ->label('إجمالي الضرائب')
                                    ->default(function ($record) {
                                        $ticket = $record->ticket;
                                        if (!$ticket) return '0.00';

                                        $totalTaxes = ($ticket->cost_tax_amount ?? 0) + ($ticket->extra_tax_amount ?? 0);
                                        return number_format($totalTaxes, 2);
                                    })
                                    ->disabled()
                                    ->dehydrated(),

                                TextInput::make('sale_total_amount')
                                    ->label('سعر البيع')
                                    ->default(fn($record) => number_format($record->ticket->sale_total_amount ?? 0, 2))
                                    ->disabled()
                                    ->dehydrated(),
                            ]),

                        // نوع الجهة
                        Select::make('statementable_type')
                            ->label('نوع الجهة')
                            ->options([
                                Client::class    => 'عميل',
                                Supplier::class  => 'مورد',
                                Branch::class    => 'فرع',
                                Franchise::class => 'فرانشايز',
                            ])
                            ->default(fn($record) => $record->statementable_type)
                            ->native(false)
                            ->required()
                            ->disabled(),

                        // الجهة
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
                            ->default(fn($record) => $record->statementable_id)
                            // ->searchable()
                            ->native(false)
                            // ->placeholder('اختر الجهة أولاً من نوع الجهة')
                            ->required()
                            ->disabled(),

                        // ملاحظات
                        Textarea::make('notes')
                            ->label('ملاحظات')
                            ->columnSpanFull(),
                    ])
                    ->action(function ($record, array $data) {
                        // التحقق من وجود فاتورة مسبقاً
                        if ($record->invoices()->exists()) {
                            Notification::make()
                                ->title('هذه التذكرة عليها فاتورة بالفعل')
                                ->danger()
                                ->send();
                            return;
                        }

                        // الحصول على التذكرة المرتبطة
                        $ticket = $record->ticket;
                        if (!$ticket) {
                            Notification::make()
                                ->title('لا توجد تذكرة مرتبطة')
                                ->danger()
                                ->send();
                            return;
                        }

                        // حساب القيم
                        $totalTaxes = ($ticket->cost_tax_amount ?? 0) + ($ticket->extra_tax_amount ?? 0);
                        $totalAmount = $ticket->sale_total_amount ?? 0;

                        // إنشاء رقم الفاتورة
                        $lastInvoiceId = Invoice::max('id') ?? 0;
                        $invoiceNumber = 'INV-' . now()->format('Y') . '-' . str_pad($lastInvoiceId + 1, 5, '0', STR_PAD_LEFT);

                        // إنشاء الفاتورة
                        $invoice = Invoice::create([
                            'type' => 'sale',
                            'is_drafted' => false,
                            'total_taxes' => $totalTaxes,
                            'total_amount' => $totalAmount,
                            'invoice_number' => $invoiceNumber,
                            'notes' => $data['notes'] ?? null,
                            'invoiceable_type' => $record->statementable_type,
                            'invoiceable_id' => $record->statementable_id,
                        ]);

                        // ربط التذكرة بالفاتورة
                        $invoice->tickets()->attach($ticket->id);

                        // تحديث حالة التذكرة
                        $ticket->update(['is_invoiced' => true]);

                        Notification::make()
                            ->title('تم إنشاء الفاتورة رقم ' . $invoiceNumber)
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalWidth('4xl')
                    ->modalHeading('إضافة فاتورة')
                    ->modalSubmitActionLabel('إنشاء الفاتورة'),
                //    ----------------------------------------------------------


                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ExportBulkAction::make()->exporter(AccountStatementExporter::class),
                    DeleteBulkAction::make(),
                    // RestoreBulkAction::make(),
                    // ForceDeleteBulkAction::make(),
                ]),
            ]);
    }
}
