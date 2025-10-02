<?php

namespace App\Filament\Resources\Tickets\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TicketForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make(__('dashboard.fields.ticket_data'))->schema([
                TextInput::make('gds')->label(__('dashboard.fields.gds')),
                TextInput::make('airline_name')->label(__('dashboard.fields.airline_name')),
                TextInput::make('validating_carrier_code')->maxLength(2)->label(__('dashboard.fields.validating_carrier_code')),
                TextInput::make('ticket_number_full')->label(__('dashboard.fields.ticket_number_full')),
                TextInput::make('ticket_number_prefix')->label(__('dashboard.fields.ticket_number_prefix')),
                TextInput::make('ticket_number_core')->label(__('dashboard.fields.ticket_number_core')),
                TextInput::make('pnr')->label(__('dashboard.fields.pnr')),
                DatePicker::make('issue_date')->label(__('dashboard.fields.issue_date')),
                DatePicker::make('booking_date')->label(__('dashboard.fields.booking_date')),
                TextInput::make('ticket_type')->label(__('dashboard.fields.ticket_type')),
                TextInput::make('ticket_type_code')->label(__('dashboard.fields.ticket_type_code')),
                TextInput::make('trip_type')->label(__('dashboard.fields.trip_type')),
                Toggle::make('is_domestic_flight')->label(__('dashboard.fields.is_domestic_flight')),
                TextInput::make('itinerary_string')->label(__('dashboard.fields.itinerary_string')),
                TextInput::make('fare_basis_out')->label(__('dashboard.fields.fare_basis_out')),
                TextInput::make('fare_basis_in')->label(__('dashboard.fields.fare_basis_in')),
                TextInput::make('branch_code')->label(__('dashboard.fields.branch_code')),
                TextInput::make('office_id')->label(__('dashboard.fields.office_id')),
                TextInput::make('created_by_user')->label(__('dashboard.fields.created_by_user')),
                TextInput::make('carrier_pnr_carrier')->label(__('dashboard.fields.carrier_pnr_carrier'))->maxLength(10),
                TextInput::make('carrier_pnr')->label(__('dashboard.fields.carrier_pnr')),
            ]),

            Section::make(__('dashboard.fields.costs_and_relations'))->schema([
                TextInput::make('cost_base_amount')
                    ->numeric()
                    ->lazy()
                    ->afterStateUpdated(fn ($state, callable $set, callable $get) => 
                    $set('cost_total_amount', ($state ?? 0) + ($get('cost_tax_amount') ?? 0))
                    + $set('sale_total_amount',
                        (($state ?? 0) + ($get('cost_tax_amount') ?? 0)) + // cost_total_amount الجديد
                        ($get('profit_amount') ?? 0) +
                        ($get('extra_tax_amount') ?? 0) -
                        ($get('discount_amount') ?? 0)
                    )
                )
            
                    ->label(__('dashboard.fields.cost_base_amount')),

                TextInput::make('cost_tax_amount')
                    ->numeric()
                    ->lazy()
                    ->afterStateUpdated(fn ($state, callable $set, callable $get) =>
                    $set('cost_total_amount', ($get('cost_base_amount') ?? 0) + ($state ?? 0))
                    + $set('sale_total_amount',
                        (($get('cost_base_amount') ?? 0) + ($state ?? 0)) + // cost_total_amount الجديد
                        ($get('profit_amount') ?? 0) +
                        ($get('extra_tax_amount') ?? 0) -
                        ($get('discount_amount') ?? 0)
                    )
                )
                                ->label(__('dashboard.fields.cost_tax_amount')),

                TextInput::make('cost_total_amount')
                    ->numeric()
                    ->disabled()
                    ->dehydrated()
                    ->label(__('dashboard.fields.cost_total_amount')),

                TextInput::make('profit_amount')
                    ->numeric()
                    ->lazy()
                    ->afterStateUpdated(fn ($state, callable $set, callable $get) =>
                        $set('sale_total_amount',
                            ($get('cost_total_amount') ?? 0) +
                            ($state ?? 0) +
                            ($get('extra_tax_amount') ?? 0) -
                            ($get('discount_amount') ?? 0)
                        )
                    )
                    ->label(__('dashboard.fields.profit_amount')),

                TextInput::make('discount_amount')
                    ->numeric()
                    ->lazy()
                    ->afterStateUpdated(fn ($state, callable $set, callable $get) =>
                        $set('sale_total_amount',
                            ($get('cost_total_amount') ?? 0) +
                            ($get('profit_amount') ?? 0) +
                            ($get('extra_tax_amount') ?? 0) -
                            ($state ?? 0)
                        )
                    )
                    ->label(__('dashboard.fields.discount_amount')),

                TextInput::make('extra_tax_amount')
                    ->numeric()
                    ->lazy()
                    ->afterStateUpdated(fn ($state, callable $set, callable $get) =>
                        $set('sale_total_amount',
                            ($get('cost_total_amount') ?? 0) +
                            ($get('profit_amount') ?? 0) +
                            ($state ?? 0) -
                            ($get('discount_amount') ?? 0)
                        )
                    )
                    ->label(__('dashboard.fields.extra_tax_amount')),

                TextInput::make('sale_total_amount')
                    ->numeric()
                    ->disabled()
                    ->dehydrated()
                    ->label(__('dashboard.fields.sale_total_amount')),

                // TextInput::make('price_taxes_breakdown')->label(__('dashboard.fields.price_taxes_breakdown')),

                Section::make('العلاقات')->schema([
                    Select::make('airline_id')
                        ->label(__('dashboard.fields.airline_name'))
                        ->relationship('airline', 'name')
                        ->searchable(),

                    Select::make('currency_id')
                        ->label(__('dashboard.fields.currency_code'))
                        ->relationship('currency', 'symbol')
                        ->searchable(),

                    Select::make('supplier_id')
                        ->label(__('dashboard.fields.supplier_name'))
                        ->relationship('supplier', 'name')
                        ->searchable(),

                    Select::make('client_id')
                        ->label(__('dashboard.fields.client_name'))
                        ->relationship('client', 'name')
                        ->searchable(),

                    Select::make('branch_id')
                        ->label(__('dashboard.fields.branch_name'))
                        ->relationship('branch', 'name')
                        ->searchable(),

                    Select::make('franchise_id')
                        ->label(__('dashboard.fields.franchise_name'))
                        ->relationship('franchise', 'name')
                        ->searchable(),

                    Select::make('sales_user_id')
                        ->label(__('dashboard.fields.sales_user_name'))
                        ->relationship('salesAgent', 'name')
                        ->searchable(),
                ]),
            ]),
        ]);
    }
}
