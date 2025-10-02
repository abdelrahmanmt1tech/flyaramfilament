<?php

namespace App\Filament\Pages;

use App\Models\AccountStatement;
use App\Models\Client;
use App\Models\Supplier;
use App\Models\Branch;
use App\Models\Franchise;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
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
                    ->getStateUsing(fn($record) => is_array($record->passengers) ? implode(', ', $record->passengers) : $record->passengers),

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
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ]);
    }
}
