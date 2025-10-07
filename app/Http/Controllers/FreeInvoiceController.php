<?php

namespace App\Http\Controllers;

use App\Models\FreeInvoice;
use Illuminate\Http\Request;

class FreeInvoiceController extends Controller
{
    public function print(FreeInvoice $freeInvoice)
    {
        if ($freeInvoice->free_invoiceable_type && $freeInvoice->free_invoiceable_type !== 'other') {
            $freeInvoice->load('freeInvoiceable');
        }
        return view('free_invoices.invoice', [
            'invoice' => $freeInvoice,
        ]);
    }
}
