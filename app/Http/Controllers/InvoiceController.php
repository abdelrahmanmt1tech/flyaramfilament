<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function printInvoice(Invoice $invoice)
    {
        $tickets = $invoice->tickets()->with(['segments', 'passengers', 'currency'])->get();
    
        return view('invoices.invoice', compact('tickets', 'invoice'));
    }
    
    
}
