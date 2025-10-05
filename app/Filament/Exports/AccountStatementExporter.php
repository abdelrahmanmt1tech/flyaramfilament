<?php

namespace App\Filament\Exports;

use App\Models\AccountStatement;
use App\Models\Branch;
use App\Models\Client;
use App\Models\Franchise;
use App\Models\Supplier;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class AccountStatementExporter extends Exporter
{
    protected static ?string $model = AccountStatement::class;

    public static function getName(): string
    {
        return __('dashboard.fields.account_statement');
    }

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label(__('dashboard.fields.id')),

            ExportColumn::make('statementable_type')
                ->label(__('dashboard.fields.statementable_type'))
                ->formatStateUsing(fn($state) => match ($state) {
                    Client::class => 'عميل',
                    Supplier::class => 'مورد',
                    Branch::class => 'فرع',
                    Franchise::class => 'فرانشايز',
                    default => $state
                }),

            ExportColumn::make('statementable_id')
                ->label(__('dashboard.fields.statementable_id'))
                ->getStateUsing(fn($record) => $record->statementable?->name),

            ExportColumn::make('date')
                ->label(__('dashboard.fields.date')),

            ExportColumn::make('doc_no')
                ->label(__('dashboard.fields.doc_no')),

            ExportColumn::make('ticket_id')
                ->label(__('dashboard.fields.ticket_id'))
                ->getStateUsing(fn($record) => $record->ticket?->ticket_number_core),

            ExportColumn::make('lpo_no')
                ->label(__('dashboard.fields.lpo_no')),

            ExportColumn::make('passengers')
                ->label(__('dashboard.fields.passengers'))
                ->getStateUsing(fn($record) => $record->ticket?->passengers->pluck('first_name')->implode(', ')),

            ExportColumn::make('sector')
                ->label(__('dashboard.fields.sector')),

            ExportColumn::make('debit')
                ->label(__('dashboard.fields.debit')),

            ExportColumn::make('credit')
                ->label(__('dashboard.fields.credit')),

            ExportColumn::make('balance')
                ->label(__('dashboard.fields.balance')),

            ExportColumn::make('created_at')
                ->label(__('dashboard.fields.created_at')),

            ExportColumn::make('updated_at')
                ->label(__('dashboard.fields.updated_at')),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your account statement export has completed and ' . Number::format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
