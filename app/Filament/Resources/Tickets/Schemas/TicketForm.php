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
                TextInput::make('gds')
                    ->label(__('dashboard.fields.gds')),
                TextInput::make('airline_name')
                    ->label(__('dashboard.fields.airline_name')),
                TextInput::make('validating_carrier_code')
                    ->length(2)
                    ->label(__('dashboard.fields.validating_carrier_code')),
                TextInput::make('ticket_number_full')
                    ->label(__('dashboard.fields.ticket_number_full')),
                TextInput::make('ticket_number_prefix')
                    ->label(__('dashboard.fields.ticket_number_prefix')),
                TextInput::make('ticket_number_core')
                    ->label(__('dashboard.fields.ticket_number_core')),
                TextInput::make('pnr')
                    ->label(__('dashboard.fields.pnr')),
                DatePicker::make('issue_date')
                    ->label(__('dashboard.fields.issue_date')),
                DatePicker::make('booking_date')
                    ->label(__('dashboard.fields.booking_date')),
                TextInput::make('ticket_type')
                    ->label(__('dashboard.fields.ticket_type')),
                TextInput::make('ticket_type_code')
                    ->label(__('dashboard.fields.ticket_type_code')),
                TextInput::make('trip_type')
                    ->label(__('dashboard.fields.trip_type')),
                Toggle::make('is_domestic_flight')
                    ->label(__('dashboard.fields.is_domestic_flight')),
                TextInput::make('itinerary_string')
                    ->label(__('dashboard.fields.itinerary_string')),
                TextInput::make('fare_basis_out')
                    ->label(__('dashboard.fields.fare_basis_out')),
                TextInput::make('fare_basis_in')
                    ->label(__('dashboard.fields.fare_basis_in')),
                TextInput::make('branch_code')
                    ->label(__('dashboard.fields.branch_code')),
                TextInput::make('office_id')
                    ->label(__('dashboard.fields.office_id')),
                TextInput::make('created_by_user')
                    ->label(__('dashboard.fields.created_by_user')),
                TextInput::make('airline_id')
                    ->label(__('dashboard.fields.airline_id'))
                    ->numeric(),
                TextInput::make('currency_id')
                    ->label(__('dashboard.fields.currency_id'))
                    ->numeric(),
                TextInput::make('supplier_id')
                    ->label(__('dashboard.fields.supplier_id'))
                    ->numeric(),
                TextInput::make('sales_user_id')
                    ->label(__('dashboard.fields.sales_user_id'))
                    ->numeric(),
                TextInput::make('client_id')
                    ->label(__('dashboard.fields.client_id'))
                    ->numeric(),
                TextInput::make('branch_id')
                    ->label(__('dashboard.fields.branch_id'))
                    ->numeric(),
                TextInput::make('cost_base_amount')
                    ->label(__('dashboard.fields.cost_base_amount'))
                    ->numeric(),
                TextInput::make('cost_tax_amount')
                    ->label(__('dashboard.fields.cost_tax_amount'))
                    ->numeric(),
                TextInput::make('cost_total_amount')
                    ->label(__('dashboard.fields.cost_total_amount'))
                    ->numeric(),
                TextInput::make('profit_amount')
                    ->label(__('dashboard.fields.profit_amount'))
                    ->numeric(),
                TextInput::make('discount_amount')
                    ->label(__('dashboard.fields.discount_amount'))
                    ->numeric(),
                TextInput::make('extra_tax_amount')
                    ->label(__('dashboard.fields.extra_tax_amount'))
                    ->numeric(),
                TextInput::make('sale_total_amount')
                    ->label(__('dashboard.fields.sale_total_amount'))
                    ->numeric(),
                TextInput::make('carrier_pnr_carrier')
                    ->label(__('dashboard.fields.carrier_pnr_carrier'))
                    ->length(10),
                TextInput::make('carrier_pnr')
                    ->label(__('dashboard.fields.carrier_pnr')),
                TextInput::make('price_taxes_breakdown')
                    ->label(__('dashboard.fields.price_taxes_breakdown')),
            ]);
    }
}
