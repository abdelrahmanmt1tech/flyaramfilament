<?php

namespace App\Filament\Resources\Tickets\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class TicketInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('gds'),
                TextEntry::make('airline_name'),
                TextEntry::make('validating_carrier_code'),
                TextEntry::make('ticket_number_full'),
                TextEntry::make('ticket_number_prefix'),
                TextEntry::make('ticket_number_core'),
                TextEntry::make('pnr'),
                TextEntry::make('issue_date')
                    ->date(),
                TextEntry::make('booking_date')
                    ->date(),
                TextEntry::make('ticket_type'),
                TextEntry::make('ticket_type_code'),
                TextEntry::make('trip_type'),
                IconEntry::make('is_domestic_flight')
                    ->boolean(),
                TextEntry::make('itinerary_string'),
                TextEntry::make('fare_basis_out'),
                TextEntry::make('fare_basis_in'),
                TextEntry::make('branch_code'),
                TextEntry::make('office_id'),
                TextEntry::make('created_by_user'),
                TextEntry::make('airline_id')
                    ->numeric(),
                TextEntry::make('currency_id')
                    ->numeric(),
                TextEntry::make('supplier_id')
                    ->numeric(),
                TextEntry::make('sales_user_id')
                    ->numeric(),
                TextEntry::make('client_id')
                    ->numeric(),
                TextEntry::make('branch_id')
                    ->numeric(),
                TextEntry::make('cost_base_amount')
                    ->numeric(),
                TextEntry::make('cost_tax_amount')
                    ->numeric(),
                TextEntry::make('cost_total_amount')
                    ->numeric(),
                TextEntry::make('profit_amount')
                    ->numeric(),
                TextEntry::make('discount_amount')
                    ->numeric(),
                TextEntry::make('extra_tax_amount')
                    ->numeric(),
                TextEntry::make('sale_total_amount')
                    ->numeric(),
                TextEntry::make('carrier_pnr_carrier'),
                TextEntry::make('carrier_pnr'),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
                TextEntry::make('deleted_at')
                    ->dateTime(),
            ]);
    }
}
