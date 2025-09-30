<?php

namespace App\Filament\Resources\Tickets\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TicketForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('gds'),
                TextInput::make('airline_name'),
                TextInput::make('validating_carrier_code'),
                TextInput::make('ticket_number_full'),
                TextInput::make('ticket_number_prefix'),
                TextInput::make('ticket_number_core'),
                TextInput::make('pnr'),
                DatePicker::make('issue_date'),
                DatePicker::make('booking_date'),
                TextInput::make('ticket_type'),
                TextInput::make('ticket_type_code'),
                TextInput::make('trip_type'),
                Toggle::make('is_domestic_flight'),
                TextInput::make('itinerary_string'),
                TextInput::make('fare_basis_out'),
                TextInput::make('fare_basis_in'),
                TextInput::make('branch_code'),
                TextInput::make('office_id'),
                TextInput::make('created_by_user'),
                TextInput::make('airline_id')
                    ->numeric(),
                TextInput::make('currency_id')
                    ->numeric(),
                TextInput::make('supplier_id')
                    ->numeric(),
                TextInput::make('sales_user_id')
                    ->numeric(),
                TextInput::make('client_id')
                    ->numeric(),
                TextInput::make('branch_id')
                    ->numeric(),
                TextInput::make('cost_base_amount')
                    ->numeric(),
                TextInput::make('cost_tax_amount')
                    ->numeric(),
                TextInput::make('cost_total_amount')
                    ->numeric(),
                TextInput::make('profit_amount')
                    ->numeric(),
                TextInput::make('discount_amount')
                    ->numeric(),
                TextInput::make('extra_tax_amount')
                    ->numeric(),
                TextInput::make('sale_total_amount')
                    ->numeric(),
                TextInput::make('carrier_pnr_carrier'),
                TextInput::make('carrier_pnr'),
                TextInput::make('price_taxes_breakdown'),
            ]);
    }
}
