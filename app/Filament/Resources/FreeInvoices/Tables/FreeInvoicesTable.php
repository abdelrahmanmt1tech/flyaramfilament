<?php

namespace App\Filament\Resources\FreeInvoices\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\Action;

use App\Models\Client;
use App\Models\Supplier;
use App\Models\Branch;
use App\Models\Franchise;

class FreeInvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('free_invoiceable_type')
                    ->label('نوع الجهة')
                    ->formatStateUsing(function ($state) {
                        return match ($state) {
                            Client::class => 'عميل',
                            Supplier::class => 'مورد',
                            Branch::class => 'فرع',
                            Franchise::class => 'فرانشايز',
                            default => $state,
                        };
                    })
                    ->sortable(),

                TextColumn::make('display_name')
                    ->label('اسم المستفيد')
                    ->getStateUsing(function ($record) {
                        if ($record->free_invoiceable_type === 'other') {
                            return $record->beneficiary_name;
                        }

                        return optional($record->freeInvoiceable)->name ?? '-';
                    })
                    ->sortable()
                    ->searchable(),

                TextColumn::make('display_tax')
                    ->label('الرقم الضريبي')
                    ->getStateUsing(function ($record) {
                        if ($record->free_invoiceable_type === 'other') {
                            return $record->beneficiary_tax_number;
                        }

                        return optional($record->freeInvoiceable)->tax_number ?? '-';
                    }),

                TextColumn::make('display_phone')
                    ->label('الهاتف')
                    ->getStateUsing(function ($record) {
                        if ($record->free_invoiceable_type === 'other') {
                            return $record->beneficiary_phone;
                        }

                        return $record->freeInvoiceable?->contactInfos?->first()?->phone ?? '-';
                    }),

                TextColumn::make('display_email')
                    ->label('البريد الإلكتروني')
                    ->getStateUsing(function ($record) {
                        if ($record->free_invoiceable_type === 'other') {
                            return $record->beneficiary_email;
                        }

                        return $record->freeInvoiceable?->contactInfos?->first()?->email ?? '-';
                    }),

                TextColumn::make('taxType.name')
                    ->getStateUsing(function ($record) {
                        return $record->tax_type_id ? $record->taxType?->name . ' (' . $record->taxType?->value . '%)' : '-';
                    })
                    ->label('نوع الضريبة')
                    ->placeholder('-')
                    ->sortable(),


                TextColumn::make('total')
                    ->label('الإجمالي')
                    ->money('SAR')
                    ->sortable(),

                TextColumn::make('issue_date')
                    ->label('تاريخ الإصدار')
                    ->date()
                    ->sortable(),

                TextColumn::make('due_date')
                    ->label('تاريخ الاستحقاق')
                    ->date()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('print')
                    ->label('طباعة')
                    ->icon('heroicon-o-printer')
                    ->url(fn($record) => route('free-invoices.print', $record))
                    ->openUrlInNewTab(),
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
