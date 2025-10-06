<?php

namespace App\Filament\Widgets;

use App\Models\Branch;
use App\Models\Client;
use App\Models\Franchise;
use App\Models\Supplier;
use App\Models\Ticket;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {

        $tickets_without_invoices = Ticket:: whereDoesntHave("invoices")->count();
        $tickets_invoices = Ticket:: whereHas("invoices")->count();
        $client = Client::count();
        $branch = Branch::count();
        $supplier = Supplier::count();
        $franchise = Franchise::count();


        return [

            Stat::make('تذاكر غير مفوتره', $tickets_without_invoices)
                ->description('عدد التذاكر غير مرتبطه بفواتير')
                ->descriptionIcon(Heroicon::ArrowTrendingUp),


            Stat::make('تذاكر  مفوتره', $tickets_invoices)
                ->description('عدد التذاكر  مرتبطه بفواتير')
                ->descriptionIcon(Heroicon::ArrowTrendingDown),


            Stat::make('عدد العملاء', $client)
                ->description('عدد التذاكر  مرتبطه بفواتير')
                ->descriptionIcon(Heroicon::ArrowLongUp),


            Stat::make('عدد الافرع', $branch)
                ->description('عدد التذاكر  مرتبطه بفواتير')
                ->descriptionIcon(Heroicon::ArrowLongUp),


            Stat::make('عدد اتلموردين', $supplier)
                ->description('عدد التذاكر  مرتبطه بفواتير')
                ->descriptionIcon(Heroicon::ArrowLongUp),


            Stat::make('عدد الفرنشايز', $franchise)
                ->description('عدد التذاكر  مرتبطه بفواتير')
                ->descriptionIcon(Heroicon::ArrowLongUp),





        ];
    }
}
