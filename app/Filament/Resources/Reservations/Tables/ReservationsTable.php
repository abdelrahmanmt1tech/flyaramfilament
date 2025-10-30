<?php

namespace App\Filament\Resources\Reservations\Tables;

use App\Models\AccountStatement;
use App\Models\Invoice;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Placeholder;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReservationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): Builder {
                return $query->with(['items', 'related', 'passenger', 'ticket']);
            })
            ->columns([
                // Reservation Number
                TextColumn::make('reservation_number')
                    ->label('رقم الحجز')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                // Reservation Type
                TextColumn::make('items.reservation_type')
                    ->label('نوع الحجز')
                    ->badge()
                    ->colors([
                        'success' => 'hotel',
                        'warning' => 'car',
                        'info' => 'tourism',
                        'primary' => 'visa',
                        'secondary' => 'international_license',
                        'gray' => 'train',
                        'purple' => 'meeting_room',
                        'pink' => 'internal_transport',
                        'indigo' => 'other',
                    ])
                    ->formatStateUsing(fn($state) => match ($state) {
                        'hotel' => 'فندق',
                        'car' => 'سيارة',
                        'tourism' => 'سياحة',
                        'visa' => 'تأشيرات',
                        'international_license' => 'رخصة قيادة دولية',
                        'train' => 'حجز قطار',
                        'meeting_room' => 'حجز قاعة إجتماعات',
                        'internal_transport' => 'تنقلات داخلية وأخرى',
                        'other' => 'أخرى',
                        default => $state
                    })
                    ->sortable(),

                TextColumn::make('taxType.name')
                    ->getStateUsing(function ($record) {
                        return $record->tax_type_id ? $record->taxType?->name . ' (' . $record->taxType?->value . '%)' : '-';
                    })
                    ->label('نوع الضريبة')
                    ->placeholder('-')
                    ->sortable(),


                TextColumn::make('total_with_tax')
                    ->label('إجمالي مع الضريبة')
                    ->getStateUsing(function ($record) {
                        return $record->getTotalWithTaxAttribute();
                    })
                    ->sortable(),

                // Actor (المسافر)
                TextColumn::make('passenger.first_name')
                    ->label('المسافر')
                    ->placeholder('-')
                    ->searchable()
                    ->sortable(),

                // Related (الجهة)
                TextColumn::make('related.name')
                    ->label('الجهة')
                    ->placeholder('-')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                // TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('createReservationInvoice')
                    ->label('إنشاء فاتورة')
                    ->icon('heroicon-o-receipt-percent')
                    ->visible(fn($record) => !Invoice::where('reservation_id', $record->id)->exists())
                    ->schema([
                        TextInput::make('reservation_number')
                            ->label('رقم الحجز')
                            ->default(fn($record) => $record->reservation_number)
                            ->disabled()
                            ->dehydrated(),

                        TextInput::make('related_name')
                            ->label('الجهة')
                            ->default(fn($record) => $record->related?->name ?? ($record->related?->company_name ?? '-'))
                            ->disabled()
                            ->dehydrated(),

                        TextInput::make('passenger_name')
                            ->label('المسافر')
                            ->default(fn($record) => $record->passenger?->first_name ?? '-')
                            ->disabled()
                            ->dehydrated(),

                        TextInput::make('total_amount_display')
                            ->label('الإجمالي (SAR)')
                            ->default(fn($record) => number_format((float)$record->total_with_tax, 2))
                            ->disabled()
                            ->dehydrated(),

                        TextInput::make('notes')
                            ->label('ملاحظات')
                            ->placeholder('ملاحظات الفاتورة (اختياري)'),
                    ])
                    ->action(function ($record, array $data) {
                        // Prevent duplicates
                        if (Invoice::where('reservation_id', $record->id)->exists()) {
                            Notification::make()
                                ->title('هذا الحجز عليه فاتورة بالفعل')
                                ->danger()
                                ->send();
                            return;
                        }

                        $sum = (float) $record->total_with_tax;
                        if ($sum <= 0) {
                            Notification::make()
                                ->title('لا يمكن إنشاء فاتورة بإجمالي 0')
                                ->danger()
                                ->send();
                            return;
                        }

                        $lastInvoiceId = Invoice::max('id') ?? 0;
                        $invoiceNumber = 'INV-' . now()->format('Y') . '-' . str_pad($lastInvoiceId + 1, 5, '0', STR_PAD_LEFT);

                        Invoice::create([
                            'type'             => 'sale',
                            'is_drafted'       => false,
                            'total_taxes'      => 0,
                            'total_amount'     => $sum,
                            'invoice_number'   => $invoiceNumber,
                            'notes'            => $data['notes'] ?? null,
                            'invoiceable_type' => $record->related_type,
                            'invoiceable_id'   => $record->related_id,
                            'reservation_id'   => $record->id,
                        ]);

                        Notification::make()
                            ->title('تم إنشاء الفاتورة رقم ' . $invoiceNumber)
                            ->success()
                            ->send();
                    }),
                Action::make('printReservationInvoice')
                    ->label('الفاتورة')
                    ->icon('heroicon-o-printer')
                    ->visible(fn($record) => Invoice::where('reservation_id', $record->id)->exists())
                    ->url(fn($record) => route('reservations.invoices.print', Invoice::where('reservation_id', $record->id)->value('slug')))
                    ->openUrlInNewTab(),

                Action::make('printReservationRefundInvoice')
                    ->label('فاتورة الاسترجاع')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('danger')
                    ->visible(fn($record) => Invoice::where('reservation_id', $record->id)->where('type', 'refund')->exists())
                    ->url(fn($record) => route(
                        'reservations.invoices.print',
                        Invoice::where('reservation_id', $record->id)->where('type', 'refund')->value('slug')
                    ))
                    ->openUrlInNewTab(),

                Action::make('createReservationRefundInvoice')
                    ->label('إنشاء فاتورة استرجاع')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('danger')
                    ->visible(function ($record) {
                        $hasOriginal = Invoice::where('reservation_id', $record->id)
                            ->where('type', '!=', 'refund')
                            ->exists();
                        $hasRefund = Invoice::where('reservation_id', $record->id)
                            ->where('type', 'refund')
                            ->exists();
                        return $hasOriginal && !$hasRefund;
                    })
                    ->schema(function ($record) {
                        $items = $record->items;

                        if ($items->isEmpty()) {
                            return [
                                Placeholder::make('no_items')
                                    ->label('لا توجد عناصر')
                                    ->content('هذا الحجز لا يحتوي على عناصر'),
                            ];
                        }

                        $itemsOptions = [];
                        foreach ($items as $item) {
                            $type = match ($item->reservation_type) {
                                'hotel' => 'فندق',
                                'car' => 'سيارة',
                                'tourism' => 'سياحة',
                                'visa' => 'تأشيرات',
                                'international_license' => 'رخصة قيادة دولية',
                                'train' => 'حجز قطار',
                                'meeting_room' => 'حجز قاعة إجتماعات',
                                'internal_transport' => 'تنقلات داخلية وأخرى',
                                'other' => 'أخرى',
                                default => $item->reservation_type
                            };

                            $itemsOptions[$item->id] = sprintf(
                                '%s - %s SAR',
                                $type,
                                number_format((float)$item->total_amount, 2)
                            );
                        }

                        return [
                            Section::make('اختر العناصر المراد استرجاعها')
                                ->description('حدد العناصر التي تريد إنشاء فاتورة استرجاع لها')
                                ->schema([
                                    CheckboxList::make('selected_items')
                                        ->label('العناصر')
                                        ->options($itemsOptions)
                                        ->columns(1)
                                        ->required()
                                        ->minItems(1)
                                        ->default(array_keys($itemsOptions))
                                        ->bulkToggleable(),

                                    // Placeholder::make('total_info')
                                    //     ->label('معلومات الإجمالي')
                                    //     ->content(function ($get) use ($items) {
                                    //         $selectedIds = $get('selected_items') ?? [];
                                    //         if (empty($selectedIds)) {
                                    //             return 'الرجاء اختيار عنصر واحد على الأقل';
                                    //         }

                                    //         $total = $items->whereIn('id', $selectedIds)->sum('total_amount');
                                    //         return sprintf(
                                    //             'إجمالي المبلغ المسترجع: %s SAR',
                                    //             number_format((float)$total, 2)
                                    //         );
                                    //     })
                                    //     ->live(),

                                    TextInput::make('new_total_amount')
                                        ->label('إجمالي المبلغ المسترجع الجديد')
                                        ->numeric()
                                ]),
                        ];
                    })
                    ->action(function ($record, array $data) {
                        $original = Invoice::where('reservation_id', $record->id)
                            ->where('type', '!=', 'refund')
                            ->latest('id')
                            ->first();

                        if (!$original) {
                            Notification::make()
                                ->title('لا توجد فاتورة أصلية لهذا الحجز')
                                ->danger()
                                ->send();
                            return;
                        }

                        $selectedItemIds = $data['selected_items'] ?? [];

                        if (empty($selectedItemIds)) {
                            Notification::make()
                                ->title('يجب اختيار عنصر واحد على الأقل')
                                ->danger()
                                ->send();
                            return;
                        }

                        // Calculate total from selected items
                        $total = $record->items()
                            ->whereIn('id', $selectedItemIds)
                            ->sum('total_amount');

                        if ($total <= 0) {
                            Notification::make()
                                ->title('إجمالي العناصر المحددة يساوي 0')
                                ->danger()
                                ->send();
                            return;
                        }

                        $lastId = Invoice::max('id') ?? 0;
                        $refundNumber = 'INV-' . now()->format('Y') . '-' . str_pad($lastId + 1, 5, '0', STR_PAD_LEFT);

                        $refund = Invoice::create([
                            'type'             => 'refund',
                            'is_drafted'       => false,
                            'total_taxes'      => 0,
                            'total_amount'     => $data['new_total_amount'] ?? $total,
                            'invoice_number'   => $refundNumber,
                            'notes'            => null,
                            'invoiceable_type' => $record->related_type,
                            'invoiceable_id'   => $record->related_id,
                            'reservation_id'   => $record->id,
                            'reference_num'    => $original->invoice_number,
                            'items_ids'        => json_encode($selectedItemIds),
                        ]);

                        // Create account statement
                        $firstStatement = $record->accountStatement->first();
                        if ($firstStatement) {
                            AccountStatement::create([
                                'statementable_type' => $firstStatement->statementable_type,
                                'statementable_id'   => $firstStatement->statementable_id,
                                'date'               => now(),
                                'doc_no'             => $refundNumber,
                                'ticket_id'          => $firstStatement->ticket_id,
                                'lpo_no'             => $firstStatement->lpo_no,
                                'sector'             => $firstStatement->sector,
                                'debit'              => 0,
                                'credit'             => $data['new_total_amount'] ?? $total,
                                'reservation_id'     => $record->id,
                                'type'               => 'refund',
                            ]);
                        }

                        Notification::make()
                            ->title('تم إنشاء فاتورة استرجاع رقم ' . $refund->invoice_number)
                            ->body('تم استرجاع ' . count($selectedItemIds) . ' عنصر بمبلغ ' . number_format($total, 2) . ' SAR')
                            ->success()
                            ->send();
                    }),

                // ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),

            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    Action::make('createReservationsInvoices')
                        ->label('إنشاء فواتير للحجوزات')
                        ->icon('heroicon-o-receipt-percent')
                        ->accessSelectedRecords()
                        ->deselectRecordsAfterCompletion()
                        ->schema(function ($records) {
                            if ($records->isEmpty()) {
                                return [];
                            }

                            $reservationsData = [];

                            foreach ($records as $index => $reservation) {
                                // Skip if already has invoice
                                if (Invoice::where('reservation_id', $reservation->id)->exists()) {
                                    continue;
                                }

                                $sum = (float) $reservation->total_with_tax;

                                // Skip if total is 0
                                if ($sum <= 0) {
                                    continue;
                                }

                                $reservationsData[] = Section::make('حجز #' . ($index + 1))
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make("reservations.{$index}.reservation_number")
                                                    ->label('رقم الحجز')
                                                    ->default($reservation->reservation_number)
                                                    ->disabled()
                                                    ->dehydrated(),

                                                TextInput::make("reservations.{$index}.related_name")
                                                    ->label('الجهة')
                                                    ->default($reservation->related?->name ?? ($reservation->related?->company_name ?? '-'))
                                                    ->disabled()
                                                    ->dehydrated(),

                                                TextInput::make("reservations.{$index}.passenger_name")
                                                    ->label('المسافر')
                                                    ->default($reservation->passenger?->first_name ?? '-')
                                                    ->disabled()
                                                    ->dehydrated(),

                                                TextInput::make("reservations.{$index}.total_amount")
                                                    ->label('الإجمالي (SAR)')
                                                    ->default(number_format($sum, 2))
                                                    ->disabled()
                                                    ->dehydrated(),
                                            ]),

                                        TextInput::make("reservations.{$index}.notes")
                                            ->label('ملاحظات')
                                            ->placeholder('ملاحظات الفاتورة (اختياري)')
                                            ->columnSpanFull(),
                                    ])
                                    ->collapsible()
                                    ->compact();
                            }

                            if (empty($reservationsData)) {
                                return [
                                    Placeholder::make('no_valid_reservations')
                                        ->label('لا توجد حجوزات صالحة')
                                        ->content('جميع الحجوزات المحددة إما لديها فواتير بالفعل أو إجماليها 0')
                                ];
                            }

                            return $reservationsData;
                        })
                        ->action(function ($records, array $data) {
                            if ($records->isEmpty()) {
                                Notification::make()
                                    ->title('لم يتم اختيار أي حجوزات')
                                    ->danger()
                                    ->send();
                                return;
                            }

                            $lastInvoiceId = Invoice::max('id') ?? 0;
                            $created = 0;

                            foreach ($records as $index => $reservation) {
                                if (Invoice::where('reservation_id', $reservation->id)->exists()) {
                                    continue;
                                }

                                $sum = (float) $reservation->total_with_tax;

                                if ($sum <= 0) {
                                    continue;
                                }

                                $lastInvoiceId++;
                                $invoiceNumber = 'INV-' . now()->format('Y') . '-' . str_pad($lastInvoiceId, 5, '0', STR_PAD_LEFT);

                                Invoice::create([
                                    'type'             => 'sale',
                                    'is_drafted'       => false,
                                    'total_taxes'      => 0,
                                    'total_amount'     => $sum,
                                    'invoice_number'   => $invoiceNumber,
                                    'notes'            => $data['reservations'][$index]['notes'] ?? null,
                                    'invoiceable_type' => $reservation->related_type,
                                    'invoiceable_id'   => $reservation->related_id,
                                    'reservation_id'   => $reservation->id,
                                ]);

                                $created++;
                            }

                            if ($created === 0) {
                                Notification::make()
                                    ->title('لم يتم إنشاء أي فاتورة')
                                    ->warning()
                                    ->send();
                                return;
                            }

                            Notification::make()
                                ->title('تم إنشاء ' . $created . ' فاتورة')
                                ->success()
                                ->send();
                        }),
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    // RestoreBulkAction::make(),

                ]),
            ]);
    }
}
