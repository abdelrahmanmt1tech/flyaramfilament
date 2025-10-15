<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Dashboard Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used by the dashboard for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
    */

    'sidebar' => [
        'airlines' => 'Airlines',
        'airports' => 'Airports',
        'airport_routes' => 'Airport Routes',
        'branches' => 'Branches',
        'classifications' => 'Classifications',
        'clients' => 'Clients',
        'currencies' => 'Currencies',
        'franchises' => 'Franchises',
        'lead_sources' => 'Lead Sources',
        'passengers' => 'Passengers',
        'suppliers' => 'Suppliers',
        'tax_types' => 'Tax Types',
        'ticket_passengers' => 'Ticket Passengers',
        'ticket_segments' => 'Ticket Segments',
        'tickets' => 'Tickets',
        'upload_tickets' => 'Upload Tickets',
        'account_statement' => 'Account Statement',
        'free_invoices' => 'Free Invoices',
        'reservations' => 'Reservations',
        'roles' => 'Roles',


    ],

    'upload_ticket' => [
        'form' => [
            'file_label' => 'Upload TXT File',
            'file_required' => 'This field is required',
        ],

        'messages' => [
            'select_file' => 'Please select a file',
            'zip_open_error' => 'Failed to open ZIP file',
            'file_not_found' => 'File not found on disk',
            'import_success' => 'Ticket(s) imported successfully',
            'import_error' => 'An error occurred during import',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Common Form Fields
    |--------------------------------------------------------------------------
    |
    | These are commonly used field labels that appear in multiple resources.
    | Keep them here for consistency and easy maintenance.
    |
    */

    'fields' => [
        'name' => 'Name',
        'name_ar' => 'Name (Arabic)',
        'name_en' => 'Name (English)',
        'code' => 'Code',
        'code_string' => 'Code String',
        'is_internal' => 'Is Internal',
        // Airline specific fields
        'iata_code' => 'IATA Code',
        'iata_prefix' => 'IATA Prefix',
        'icao_code' => 'ICAO Code',
        'created_date' => 'Created Date',
        'last_modified_date' => 'Last Modified Date',
        'status' => 'Status',
        'description' => 'Description',
        'phone' => 'Phone',
        'email' => 'Email',
        'address' => 'Address',
        'active' => 'Active',
        'inactive' => 'Inactive',
        // Client specific fields
        'company_name' => 'Company Name',
        'tax_number' => 'Tax Number',
        'sales_rep' => 'Sales Representative',
        'lead_source' => 'Lead Source',
        'contact_infos' => 'Contact Information',
        // Currency specific fields
        'symbol' => 'Symbol',
        // Airport specific fields
        'iata' => 'IATA Code',
        'city' => 'City',
        'country_code' => 'Country Code',
        'airline_info' => 'Airline Info',
        'airport_info' => 'Airport Info',
        // Classification specific fields
        'type' => 'Type',
        // Branch specific fields
        'tax_number' => 'Tax Number',
        'basic_info' => 'Basic Info',
        'contact_info' => 'Contact Info',
        'add_contact_info' => 'Add Contact Info',
        'phone' => 'Phone',
        // Passenger specific fields
        'passport_expiry' => 'Passport Expiry',
        'passport_number' => 'Passport Number',
        'nationality' => 'Nationality',
        'date_of_birth' => 'Date of Birth',
        'company_name_ar' => 'Company Name (Arabic)',
        'company_name_en' => 'Company Name (English)',
        // Passenger specific fields
        'first_name' => 'First Name',
        'last_name' => 'Last Name',
        'ticket_number_core' => 'Ticket Number Core',
        // Ticket specific fields
        'gds' => 'Global Distribution System',
        'airline_name' => 'Airline Name',
        'validating_carrier_code' => 'Validating Carrier Code',
        'ticket_number_full' => 'Full Ticket Number',
        'ticket_number_prefix' => 'Ticket Number Prefix',
        'pnr' => 'Passenger Name Record',
        'issue_date' => 'Issue Date',
        'booking_date' => 'Booking Date',
        'ticket_type' => 'Ticket Type',
        'ticket_type_code' => 'Ticket Type Code',
        'trip_type' => 'Trip Type',
        'is_domestic_flight' => 'Domestic Flight',
        'itinerary_string' => 'Flight Itinerary',
        'fare_basis_out' => 'Fare Basis Outbound',
        'fare_basis_in' => 'Fare Basis Inbound',
        'branch_code' => 'Branch Code',
        'office_id' => 'Office ID',
        'created_by_user' => 'Created By User',
        'airline_id' => 'Airline ID',
        'currency_id' => 'Currency ID',
        'supplier_id' => 'Supplier ID',
        'sales_user_id' => 'Sales User ID',
        'client_id' => 'Client ID',
        'branch_id' => 'Branch ID',
        'cost_base_amount' => 'Base Cost',
        'cost_tax_amount' => 'Tax Cost',
        'cost_total_amount' => 'Total Cost',
        'profit_amount' => 'Profit',
        'discount_amount' => 'Discount Amount',
        'extra_tax_amount' => 'Extra Tax Amount',
        'sale_total_amount' => 'Total Sale Amount',
        'carrier_pnr_carrier' => 'Carrier PNR',
        'carrier_pnr' => 'Carrier PNR',
        'price_taxes_breakdown' => 'Price Taxes Breakdown',
        // Ticket table specific labels
        'ticket_no' => 'Ticket No',
        'booking_date_label' => 'Booking Date',
        'passenger_label' => 'Passenger',
        'branch_number' => 'Branch #',
        'user_number' => 'User #',
        'type_code' => 'Type Code',
        'internal' => 'Internal',
        'cost' => 'Cost',
        'price_for_sale' => 'Price For Sale',
        'profits' => 'Profits',
        'airline_label' => 'Airline',
        'flights' => 'Flights',
        'type' => 'Type',
        'franchise_name' => 'Franchise Name',
        'branch_name' => 'Branch Name',
        'client_name' => 'Client Name',
        'supplier_name' => 'Supplier Name',
        'sales_user_name' => 'Sales User Name',
        'currency_code' => 'Currency Code',
        'ticket_data' => 'Ticket Data',
        'costs_and_relations' => 'Costs and Relations', 
        'price_taxes_breakdown' => 'Price Taxes Breakdown',
        'code' => 'Code',
        'amount' => 'Amount',
        'currency' => 'Currency',
        'id'                 => 'ID',
        'statementable_type' => 'Statementable Type',
        'statementable_id'   => 'Statementable ID',
        'date'               => 'Date',
        'doc_no'             => 'Document Number',
        'ticket_id'          => 'Ticket ID',
        'lpo_no'             => 'LPO Number',
        'passengers'         => 'Passengers',
        'sector'             => 'Sector',
        'debit'              => 'Debit',
        'credit'             => 'Credit',
        'balance'            => 'Balance',
        'created_at'         => 'Created At',
        'updated_at'         => 'Updated At',
        'account_statement' => 'Account Statement',
        // Role specific fields
        'role' => 'Role',
        'role_name' => 'Role Name',
        'permissions' => 'Permissions',
        'select_permissions' => 'Select permissions assigned to this role',
    ],
];