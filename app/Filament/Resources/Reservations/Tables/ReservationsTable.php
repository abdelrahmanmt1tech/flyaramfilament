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
                    ->formatStateUsing(fn($state) => match($state) {
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
