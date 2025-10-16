<?php

namespace App\Filament\Pages;

use App\Models\Ticket;
use App\Models\Airline;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Branch;
use App\Models\Passenger;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Summarizers\Sum;
use Illuminate\Support\Facades\Auth;

class TicketRefundsReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'تقارير مرتجعات التذاكر';
    protected static string | null | \UnitEnum $navigationGroup = 'التقارير';
    protected static ?string $title = 'تقارير مرتجعات التذاكر';
    protected static ?int $navigationSort = 1;
    

    protected static bool $shouldRegisterNavigation = true;

    protected string $view = 'filament.pages.ticket-refunds-report';

    public static function getNavigationLabel(): string
    {
        return 'تقارير مرتجعات التذاكر';
    }

    public function getTitle(): string
    {
        return 'تقارير مرتجعات التذاكر';
    }

    // public static function canAccess(): bool
    // {
    //     return true;
    // }

    public static function canAccess(): bool
    {
        return Auth::user()->can('ticket_refunds_report.view');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Ticket::query()
                    ->whereHas('invoices', function ($query) {
                        $query->where('type', 'refund');
                    })
                    ->with(['airline', 'supplier', 'salesAgent', 'branch', 'passengers', 'invoices'])
            )
            ->columns([
                TextColumn::make('ticket_number_core')
                    ->label('رقم التذكرة')
                    ->searchable(),

                TextColumn::make('airline.name')
                    ->label('شركة الطيران')
                    ->searchable(),

                TextColumn::make('itinerary_string')
                    ->label('خط السير')
                    ->limit(30)
                    ->tooltip(fn($record) => $record->itinerary_string),

                TextColumn::make('ticket_type')
                    ->label('نوع التذكرة')
                    ->badge(),

                TextColumn::make('supplier.name')
                    ->label('المورد')
                    ->searchable(),

                TextColumn::make('passengers.first_name')
                    ->label('المسافر')
                    ->getStateUsing(fn($record) => $record->passengers->pluck('first_name')->implode(', '))
                    ->limit(20),

                TextColumn::make('salesAgent.name')
                    ->label('المستخدم')
                    ->searchable(),

                TextColumn::make('branch.name')
                    ->label('الفرع')
                    ->searchable(),

                TextColumn::make('issue_date')
                    ->label('تاريخ الإصدار')
                    ->date(),

                TextColumn::make('refund_date')
                    ->label('تاريخ الاسترجاع')
                    ->getStateUsing(fn($record) => $record->invoices->where('type', 'refund')->first()?->created_at?->format('Y-m-d'))
                    ->date(),

                TextColumn::make('sale_total_amount')
                    ->label('مبلغ الاسترجاع')
                    ->money('SAR')
                    ->color('danger')
                    ->summarize([
                        Sum::make()
                            ->label('إجمالي المرتجعات')
                            ->money('SAR')
                    ]),

                TextColumn::make('original_invoice')
                    ->label('الفاتورة الأصلية')
                    ->getStateUsing(fn($record) => $record->invoices->where('type', 'refund')->first()?->reference_num),
            ])
            ->filters([
                Filter::make('filters')
                    ->schema([
                        Select::make('airline_id')
                            ->label('شركة الطيران')
                            ->options(Airline::pluck('name', 'id'))
                            ->searchable()
                            ->nullable(),

                        Select::make('itinerary')
                            ->label('خط السير')
                            ->options(function () {
                                return Ticket::distinct()
                                    ->whereNotNull('itinerary_string')
                                    ->pluck('itinerary_string', 'itinerary_string');
                            })
                            ->searchable()
                            ->nullable(),

                        Select::make('ticket_type')
                            ->label('نوع التذكرة')
                            ->options(function () {
                                return Ticket::distinct()
                                    ->whereNotNull('ticket_type')
                                    ->pluck('ticket_type', 'ticket_type');
                            })
                            ->searchable()
                            ->nullable(),

                        Select::make('supplier_id')
                            ->label('المورد')
                            ->options(Supplier::pluck('name', 'id'))
                            ->searchable()
                            ->nullable(),

                        Select::make('passenger_id')
                            ->label('المسافر')
                            ->options(Passenger::pluck('first_name', 'id'))
                            ->searchable()
                            ->nullable(),

                        Select::make('sales_user_id')
                            ->label('المستخدم')
                            ->options(User::pluck('name', 'id'))
                            ->searchable()
                            ->nullable(),

                        Select::make('branch_id')
                            ->label('الفرع')
                            ->options(Branch::pluck('name', 'id'))
                            ->searchable()
                            ->nullable(),

                        DatePicker::make('issue_date_from')
                            ->label('تاريخ الإصدار من'),

                        DatePicker::make('issue_date_to')
                            ->label('تاريخ الإصدار إلى'),

                        DatePicker::make('refund_date_from')
                            ->label('تاريخ الاسترجاع من'),

                        DatePicker::make('refund_date_to')
                            ->label('تاريخ الاسترجاع إلى'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['airline_id'] ?? null, 
                                fn($q, $airline) => $q->where('airline_id', $airline))
                            ->when($data['itinerary'] ?? null, 
                                fn($q, $itinerary) => $q->where('itinerary_string', $itinerary))
                            ->when($data['ticket_type'] ?? null, 
                                fn($q, $type) => $q->where('ticket_type', $type))
                            ->when($data['supplier_id'] ?? null, 
                                fn($q, $supplier) => $q->where('supplier_id', $supplier))
                            ->when($data['passenger_id'] ?? null, 
                                fn($q, $passenger) => $q->whereHas('passengers', fn($q) => $q->where('passengers.id', $passenger)))
                            ->when($data['sales_user_id'] ?? null, 
                                fn($q, $user) => $q->where('sales_user_id', $user))
                            ->when($data['branch_id'] ?? null, 
                                fn($q, $branch) => $q->where('branch_id', $branch))
                            ->when($data['issue_date_from'] ?? null, 
                                fn($q, $date) => $q->whereDate('issue_date', '>=', $date))
                            ->when($data['issue_date_to'] ?? null, 
                                fn($q, $date) => $q->whereDate('issue_date', '<=', $date))
                                ->when($data['refund_date_from'] ?? null, 
                                fn($q, $date) => $q->whereHas('invoices', fn($q) => $q->where('type', 'refund')->whereDate('invoices.created_at', '>=', $date)))
                            ->when($data['refund_date_to'] ?? null, 
                                fn($q, $date) => $q->whereHas('invoices', fn($q) => $q->where('type', 'refund')->whereDate('invoices.created_at', '<=', $date)));
                            
                    })
            ])
            ->headerActions([
          
            ]);
    }

}