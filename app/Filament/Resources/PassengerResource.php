<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PassengerResource\Pages;
use App\Models\Passenger;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class PassengerResource extends Resource
{
    protected static ?string $model = Passenger::class;

    protected static ?string $slug = 'passengers';

    protected static ?int $navigationSort = 65;

    protected static ?string $navigationLabel = "المسافرون";
    protected static ?string $pluralModelLabel = "المسافرون";
    protected static ?string $modelLabel = 'مسافر';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Users;

    public static function getNavigationLabel(): string
    {
        return __('dashboard.sidebar.passengers');
    }

       public static function canViewAny(): bool
    {
        return Auth::user()->can('passengers.view');
    }

    public static function canCreate(): bool
    {
        return Auth::user()->can('passengers.create');
    }

    public static function canEdit($record): bool
    {
        return Auth::user()->can('passengers.update');
    }

    public static function canDelete($record): bool
    {
        return Auth::user()->can('passengers.delete');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
            Section::make(__('dashboard.fields.basic_info'))
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                TextInput::make('first_name')
                    ->label(__('dashboard.fields.first_name'))
                    ->required(),

                TextInput::make('last_name')
                    ->label(__('dashboard.fields.last_name'))
                    ->required(),

                TextInput::make('title')
                    ->label(__('dashboard.fields.title')),

                TextInput::make('email')
                    ->label(__('dashboard.fields.email'))
                    ->email(),

                TextInput::make('phone')
                    ->label(__('dashboard.fields.phone'))
                    ->tel(),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('first_name')
                    ->label(__('dashboard.fields.first_name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('last_name')
                    ->label(__('dashboard.fields.last_name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('title')
                    ->label(__('dashboard.fields.title'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label(__('dashboard.fields.email'))
                    ->searchable(),

                TextColumn::make('phone')
                    ->label(__('dashboard.fields.phone'))
                    ->searchable(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPassengers::route('/'),
            'create' => Pages\CreatePassenger::route('/create'),
            'edit' => Pages\EditPassenger::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['first_name', 'last_name', 'email', 'phone'];
    }
}
