<?php

namespace App\Filament\Resources\Reservations\Tables;

use App\Models\AccountStatement;
use App\Models\Branch;
use App\Models\Client;
use App\Models\Franchise;
use App\Models\Supplier;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReservationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): Builder {
                return $query->with(['currency', 'actor', 'related']);
            })
            ->columns([
                // Reservation Number
                TextColumn::make('reservation_number')
                    ->label('رقم الحجز')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                // Reservation Type
                TextColumn::make('reservation_type')
                    ->label('نوع الحجز')
                    ->badge()
                    ->colors([
                        'success' => 'hotel',
                        'warning' => 'car',
                        'info' => 'tourism',
                    ])
                    ->formatStateUsing(fn($state) => match($state) {
                        'hotel' => 'فندق',
                        'car' => 'سيارة',
                        'tourism' => 'سياحة',
                        default => $state
                    })
                    ->sortable(),

                // Actor (المسافر)
                TextColumn::make('actor.first_name')
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

                // Destination
                TextColumn::make('destination')
                    ->label('الوجهة')
                    ->searchable()
                    ->sortable(),

                // Dates
                TextColumn::make('date')
                    ->label('تاريخ الحجز')
                    ->date()
                    ->sortable(),

                // Hotel Specific
                TextColumn::make('hotel_name')
                    ->label('الفندق')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),

                TextColumn::make('arrival_date')
                    ->label('تاريخ الوصول')
                    ->date()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

                // Car Specific
                TextColumn::make('service_type')
                    ->label('نوع الخدمة')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('from_date')
                    ->label('من تاريخ')
                    ->date()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

                // Financial Columns
                TextColumn::make('purchase_amount')
                    ->label('سعر الشراء')
                    ->money(fn($record) => $record->currency?->symbol ?: 'SAR', true)
                    ->sortable(),

                TextColumn::make('sale_amount')
                    ->label('سعر البيع')
                    ->badge()
                    ->color(function ($record) {
                        if ($record->sale_amount < $record->purchase_amount) {
                            return "danger";
                        }
                        return "success";
                    })
                    ->money(fn($record) => $record->currency?->symbol ?: 'SAR', true)
                    ->sortable(),

                TextColumn::make('commission_amount')
                    ->label('العمولة')
                    ->money(fn($record) => $record->currency?->symbol ?: 'SAR', true)
                    ->sortable()
                    ->toggleable(),

                // Payment Methods
                TextColumn::make('cash_payment')
                    ->label('الدفع نقداً')
                    ->money(fn($record) => $record->currency?->symbol ?: 'SAR', true)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('visa_payment')
                    ->label('الدفع بفيزا')
                    ->money(fn($record) => $record->currency?->symbol ?: 'SAR', true)
                    ->toggleable(isToggledHiddenByDefault: true),

                // Agent
                TextColumn::make('agent_name')
                    ->label('اسم الوكيل')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),

                // Additional Info
                TextColumn::make('special_requests')
                    ->label('طلبات خاصة')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('notes')
                    ->label('ملاحظات')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    // RestoreBulkAction::make(),

                ]),
            ]);
    }
}
