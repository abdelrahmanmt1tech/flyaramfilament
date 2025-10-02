<?php

namespace App\Filament\Resources\Tickets\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TicketInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make(__('dashboard.fields.ticket_data'))->schema([
                TextEntry::make('gds')->label(__('dashboard.fields.gds')),
                TextEntry::make('airline_name')->label(__('dashboard.fields.airline_name')),
                TextEntry::make('validating_carrier_code')->label(__('dashboard.fields.validating_carrier_code')),
                TextEntry::make('ticket_number_full')->label(__('dashboard.fields.ticket_number_full')),
                TextEntry::make('ticket_number_prefix')->label(__('dashboard.fields.ticket_number_prefix')),
                TextEntry::make('ticket_number_core')->label(__('dashboard.fields.ticket_number_core')),
                TextEntry::make('pnr')->label(__('dashboard.fields.pnr')),
                TextEntry::make('issue_date')->label(__('dashboard.fields.issue_date'))->date(),
                TextEntry::make('booking_date')->label(__('dashboard.fields.booking_date'))->date(),
                TextEntry::make('ticket_type')->label(__('dashboard.fields.ticket_type')),
                TextEntry::make('ticket_type_code')->label(__('dashboard.fields.ticket_type_code')),
                TextEntry::make('trip_type')->label(__('dashboard.fields.trip_type')),
                IconEntry::make('is_domestic_flight')->label(__('dashboard.fields.is_domestic_flight'))->boolean(),
                TextEntry::make('itinerary_string')->label(__('dashboard.fields.itinerary_string')),
                TextEntry::make('fare_basis_out')->label(__('dashboard.fields.fare_basis_out')),
                TextEntry::make('fare_basis_in')->label(__('dashboard.fields.fare_basis_in')),
                TextEntry::make('branch_code')->label(__('dashboard.fields.branch_code')),
                TextEntry::make('office_id')->label(__('dashboard.fields.office_id')),
                TextEntry::make('created_by_user')->label(__('dashboard.fields.created_by_user')),
                TextEntry::make('carrier_pnr_carrier')->label(__('dashboard.fields.carrier_pnr_carrier')),
                TextEntry::make('carrier_pnr')->label(__('dashboard.fields.carrier_pnr')),
            ]),

            Section::make(__('dashboard.fields.costs_and_relations'))->schema([
                TextEntry::make('cost_base_amount')->label(__('dashboard.fields.cost_base_amount'))->numeric(),
                TextEntry::make('cost_tax_amount')->label(__('dashboard.fields.cost_tax_amount'))->numeric(),
                TextEntry::make('cost_total_amount')->label(__('dashboard.fields.cost_total_amount'))->numeric(),
                TextEntry::make('profit_amount')->label(__('dashboard.fields.profit_amount'))->numeric(),
                TextEntry::make('discount_amount')->label(__('dashboard.fields.discount_amount'))->numeric(),
                TextEntry::make('extra_tax_amount')->label(__('dashboard.fields.extra_tax_amount'))->numeric(),
                TextEntry::make('sale_total_amount')->label(__('dashboard.fields.sale_total_amount'))->numeric(),
                // TextEntry::make('price_taxes_breakdown')->label(__('dashboard.fields.price_taxes_breakdown')),
                TextEntry::make('airline.name')->label(__('dashboard.fields.airline_name')),
                TextEntry::make('currency.symbol')->label(__('dashboard.fields.currency_name')),
                TextEntry::make('supplier.name')->label(__('dashboard.fields.supplier_name')),
                TextEntry::make('client.name')->label(__('dashboard.fields.client_name')),
                TextEntry::make('branch.name')->label(__('dashboard.fields.branch_name')),
                TextEntry::make('franchise.name')->label(__('dashboard.fields.franchise_name')),
                TextEntry::make('salesAgent.name')->label(__('dashboard.fields.sales_user_name')),
                ViewEntry::make('price_taxes_breakdown')
                ->label(__('dashboard.fields.price_taxes_breakdown'))
                ->view('infolists.components.price-taxes-table')
                ->columnSpanFull(),
            ]),


        ]);
    }
}
