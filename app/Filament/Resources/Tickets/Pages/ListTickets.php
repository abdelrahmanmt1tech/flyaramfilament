<?php

namespace App\Filament\Resources\Tickets\Pages;

use App\Filament\Resources\Tickets\TicketResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListTickets extends ListRecords
{
    protected static string $resource = TicketResource::class;


    public function getTabs(): array
    {
        return [
            'all' => Tab::make()
                ->label("كل التذاكر")

            ,
            'without_users' => Tab::make()
                ->label("بدون توزيع")
                ->modifyQueryUsing(fn (Builder $query) =>
                $query
                    // ->whereNull('supplier_id')
                    ->whereNull('client_id')
                    ->whereNull('branch_id')
                    ->whereNull('franchise_id')
                ),

            'without_invoices' => Tab::make()
                ->label("موزع بدون فواتير")
                ->modifyQueryUsing(fn (Builder $query) =>
                $query
                    ->where(function (Builder $query) {

                        $query 
                        //   ->whereNotNull('supplier_id')
                            ->whereNotNull('client_id')
                            ->orWhereNotNull('branch_id')
                            ->orWhereNotNull('franchise_id') ;

                    })->where("is_invoiced" ,  false)
                ),

            'with_invoices' => Tab::make()
                ->label("موزع  بفواتير")
                ->modifyQueryUsing(fn (Builder $query) =>
                $query
                    ->where(function (Builder $query) {

                        $query 
                        //   ->whereNotNull('supplier_id')
                            ->whereNotNull('client_id')
                            ->orWhereNotNull('branch_id')
                            ->orWhereNotNull('franchise_id') ;

                    })->where("is_invoiced" ,  true)
                ),





//            sales_user_id
//                    tax_type_id




        ];
    }


    public function getDefaultActiveTab(): string | int | null
    {
        return 'without_users';
    }


    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
