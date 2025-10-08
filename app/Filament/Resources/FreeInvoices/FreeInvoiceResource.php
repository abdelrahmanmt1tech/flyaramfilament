<?php

namespace App\Filament\Resources\FreeInvoices;

use App\Filament\Resources\FreeInvoices\Pages\CreateFreeInvoice;
use App\Filament\Resources\FreeInvoices\Pages\EditFreeInvoice;
use App\Filament\Resources\FreeInvoices\Pages\ListFreeInvoices;
use App\Filament\Resources\FreeInvoices\Pages\ViewFreeInvoice;
use App\Filament\Resources\FreeInvoices\Schemas\FreeInvoiceForm;
use App\Filament\Resources\FreeInvoices\Tables\FreeInvoicesTable;
use App\Models\FreeInvoice;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FreeInvoiceResource extends Resource
{
    protected static ?string $model = FreeInvoice::class;

    protected static ?string $slug = 'guest-invoices';

    protected static ?int $navigationSort = 111;

    protected static ?string $navigationLabel = "فواتير";
    protected static ?string $pluralModelLabel = "فواتير";
    protected static ?string $modelLabel = 'فاتورة';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Ticket;

    protected static ?string $recordTitleAttribute = 'beneficiary_name';

    public static function getNavigationLabel(): string
    {
        return __('dashboard.sidebar.free_invoices');
    }

    public static function form(Schema $schema): Schema
    {
        return FreeInvoiceForm::configure($schema);
    }


    public static function table(Table $table): Table
    {
        return FreeInvoicesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFreeInvoices::route('/'),
            'create' => CreateFreeInvoice::route('/create'),
            'view' => ViewFreeInvoice::route('/{record}'),
            'edit' => EditFreeInvoice::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
