<?php

namespace App\Support;

use Spatie\Permission\Models\Permission;

class PermissionRegistry
{
    public static function all(): array
    {
        // TODO: Add all your app permissions here. Example groups:
        return [
            // Users Management
            'users.view',
            'users.create',
            'users.update',
            'users.delete',

            // Roles & Permissions Management
            'roles.view',
            'roles.create',
            'roles.update',
            'roles.delete',

            // Airlines Management
            'airlines.view',
            'airlines.create',
            'airlines.update',
            'airlines.delete',

            // Airports Management
            'airports.view',
            'airports.create',
            'airports.update',
            'airports.delete',

            // Airport Routes Management
            'airport_routes.view',
            'airport_routes.create',
            'airport_routes.update',
            'airport_routes.delete',

            // Branches Management
            'branches.view',
            'branches.create',
            'branches.update',
            'branches.delete',

            // Classifications Management
            'classifications.view',
            'classifications.create',
            'classifications.update',
            'classifications.delete',

            // Clients Management
            'clients.view',
            'clients.create',
            'clients.update',
            'clients.delete',

            // Currencies Management
            'currencies.view',
            'currencies.create',
            'currencies.update',
            'currencies.delete',

            // Franchises Management
            'franchises.view',
            'franchises.create',
            'franchises.update',
            'franchises.delete',

            // Lead Sources Management
            'lead_sources.view',
            'lead_sources.create',
            'lead_sources.update',
            'lead_sources.delete',

            // Passengers Management
            'passengers.view',
            'passengers.create',
            'passengers.update',
            'passengers.delete',

            // Suppliers Management
            'suppliers.view',
            'suppliers.create',
            'suppliers.update',
            'suppliers.delete',

            // Tax Types Management
            'tax_types.view',
            'tax_types.create',
            'tax_types.update',
            'tax_types.delete',

            // Tickets Management
            'tickets.view',
            'tickets.create',
            'tickets.update',
            'tickets.delete',

            // Upload Tickets (Special permission for file upload)
            'upload_tickets.view',
            'upload_tickets.create',

            // Account Statement Management
            'account_statement.view',

            // Free Invoices Management
            'free_invoices.view',
            'free_invoices.create',
            'free_invoices.update',
            'free_invoices.delete',

            // Reservations Management
            'reservations.view',
            'reservations.create',
            'reservations.update',
            'reservations.delete',

            // Ticket Matching Management
            'ticket_matching.view',
            'ticket_matching.create',

            // Company Settings Management
            'company_settings.view',
            'company_settings.create',

            // Ticket Sales Report Management
            'ticket_sales_report.view',

            // Ticket Refunds Report Management
            'ticket_refunds_report.view',


        ];
    }

    public static function sync(string $guard = 'web'): void
    {
        $declared = collect(self::all())->unique()->values();
        $existing = Permission::query()->where('guard_name', $guard)->pluck('name');

        $toCreate = $declared->diff($existing);
        $toDelete = $existing->diff($declared);

        foreach ($toCreate as $name) {
            Permission::firstOrCreate([
                'name' => $name,
                'guard_name' => $guard,
            ]);
        }

        if ($toDelete->isNotEmpty()) {
            Permission::query()
                ->where('guard_name', $guard)
                ->whereIn('name', $toDelete->all())
                ->delete();
        }
    }
}
