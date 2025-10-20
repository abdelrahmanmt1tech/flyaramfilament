<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class InvoiceController extends Controller
{
public function printInvoice($slug)
{

    $invoice = Invoice::where('slug', $slug)->firstOrFail();

    $tickets = $invoice->tickets()->with(['segments', 'passengers', 'currency'])->get();

    $qrCode = QrCode::format('svg')
        ->size(120)
        ->generate($invoice->secure_url);

    return view('invoices.invoice', compact('tickets', 'invoice', 'qrCode'));
}

public function printReservationInvoice($slug)
{

    $invoice = Invoice::where('slug', $slug)->firstOrFail();

    $reservation = $invoice->reservation()
        ->with(['items.supplier', 'passenger', 'related'])
        ->first();

    $qrCode = QrCode::format('svg')
        ->size(120)
        ->generate($invoice->secure_url);

    return view('invoices.reservation_invoice', compact('invoice', 'reservation', 'qrCode'));
}


    public function publicView($slug, $token)
    {
        $invoice = Invoice::where('slug', $slug)
            ->where('access_token', $token)
            ->firstOrFail();

        if ($invoice->reservation_id) {
            $reservation = $invoice->reservation()
                ->with(['items.supplier', 'passenger', 'related'])
                ->first();
            return view('invoices.public_reservation_invoice', compact('invoice', 'reservation'));
        } else {
            $tickets = $invoice->tickets()->with(['segments', 'passengers', 'currency'])->get();
            return view('invoices.public_invoice', compact('invoice', 'tickets'));
        }
    }
    
    
}
