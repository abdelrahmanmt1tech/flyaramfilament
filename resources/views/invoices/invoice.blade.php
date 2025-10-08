<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ticket Invoice</title>
    <style>
        @page {
            size: A2 portrait;
            margin: 2cm;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 2cm;
            box-sizing: border-box;
            background: #fff;
            color: #000;
            width: 100%;
        }

        .container {
            max-width: 100%;
            page-break-inside: avoid;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }

        .logo {
            max-height: 100px;
        }

        h1 {
            font-size: 32px;
            margin: 0;
        }

        h3.section-title {
            background-color: #f2f2f2;
            padding: 7px;
            font-size: 15px;
            margin-top: 10px;
            border-left: 5px solid #333;
        }
        .refund {
            background-color: #f2f2f2;
            padding: 7px;
            font-size: 15px;
            margin-top: 10px;
            /* border-left: 5px solid #333; */
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            border: 1px solid #333;
            padding: 5px;
            font-size: 14px;
        }

        th {
            background-color: #e0e0e0;
            text-align: left;
        }

        .print-button {
            position: fixed;
            top: 20px;
            right: 40px;
            padding: 12px 24px;
            font-size: 16px;
            background-color: #4CAF50;
            color: #fff;
            border: none;
            cursor: pointer;
            z-index: 1000;
        }

        @media print {
            .print-button {
                display: none;
            }

            body {
                padding: 0;
                margin: 0;
            }

            .container {
                padding: 0;
                margin: 0;
            }
            hr {
                display: none;
            }
        }

        .break_page {
            break-after: page;
        }


    </style>
<body>

<button class="print-button" onclick="window.print()">🖨️ طباعة</button>

<div class="container">
    <div class="header">
        <img src="{{ asset('logo.png') }}" alt="Logo" class="logo" width="100">
        <h1>تذكرة سفر / Ticket Invoice</h1>
    </div>

    @php
        $companyKeys = [
            'company_name_en',
            'company_address_en',
            'tax_number',
            'commercial_register',
            'tourism_license',
        ];
        $company = \App\Models\Setting::whereIn('key', $companyKeys)->pluck('value', 'key');
    @endphp
@if($invoice->type === 'refund')
<h1 class="refund">REFUND INVOICE</h1>
@endif
    <h3 class="section-title">Company Details</h3>
    <table>
        <tr>
            <th>Company Name</th>
            <td>{{ $company['company_name_en'] ?? '' }}</td>
            <th>Address</th>
            <td>{{ $company['company_address_en'] ?? '' }}</td>
        </tr>
        <tr>
            <th>Tax Number</th>
            <td>{{ $company['tax_number'] ?? '' }}</td>
            <th>Commercial Register</th>
            <td>{{ $company['commercial_register'] ?? '' }}</td>
        </tr>
        <tr>
            <th>Tourism License</th>
            <td colspan="3">{{ $company['tourism_license'] ?? '' }}</td>
        </tr>
    </table>
    <h3 class="section-title">Client Details</h3>
    <table>
        @php
        $ticket = $invoice->tickets->first();
        $client = $ticket->client
            ?? $ticket->supplier
            ?? $ticket->branch
            ?? $ticket->franchise;
        @endphp
        <tr>
            <th>Client Name</th>
            <td>{{ $client->company_name ?? $client->name ?? ''}}</td>
            <th>Address</th>
            <td>{{ $client->address ?? '' }}</td>
        </tr>
        <tr>
            <th>Tax Number</th>
            <td>{{ $client->tax_number ?? '' }}</td>
        </tr>
    </table>

    @foreach($invoice->tickets as $ticket)
        @if(!$loop->first)
            <hr>
        @endif

        <h3 class="section-title">Ticket Details</h3>
        <table>
            <tr>
                <th>Airline Name</th>
                <td>{{ $ticket->airline->name ?? $ticket->airline_name }} ({{ $ticket->airline->iata_code ?? '' }})</td>

                <th>Airline Code</th>
                <td>{{ $ticket->airline->iata_prefix ?? $ticket->validating_carrier_code ?? '' }}</td>
            </tr>
            <tr>
                <th>Ticket Number</th>
                <td>{{ $ticket->ticket_number_full ?? '' }}</td>

                <th>Issue Date</th>
                <td>{{ $ticket->issue_date ? $ticket->issue_date->format('Y-m-d') : '' }}</td>
            </tr>
            <tr>
                <th>Itinerary</th>
                <td colspan="3">
                    @if($ticket->segments->count() > 0)
                        {{ $ticket->segments->pluck('origin.iata')->join(' → ') }}
                        →
                        {{ $ticket->segments->last()->destination->iata }}
                    @else
                        {{ $ticket->itinerary_string ?? 'N/A' }}
                    @endif
                </td>
            </tr>
            <tr>
                <th>Beneficiary</th>
                <td>{{ $ticket->client->name ?? 'N/A' }}</td>

                <th>PNR</th>
                <td>{{ $ticket->pnr ?? '' }}</td>
            </tr>
        </table>

        @if($ticket->segments->count() > 0)
            @foreach($ticket->segments as $segment)
                <h3 class="section-title">Flight Details</h3>
                <table>
                    <tr>
                        <th>Flight Number</th>
                        <td>{{ $segment->flight_number ?? '' }}</td>
                        <th>Ticket Number</th>
                        <td>{{ $ticket->ticket_number_core ?? '' }}</td>
                    </tr>
                    <tr>
                        <th>From</th>
                        <td>{{ $segment->origin->city ?? $segment->origin->iata ?? '' }}</td>
                        <th>To</th>
                        <td>{{ $segment->destination->city ?? $segment->destination->iata ?? '' }}</td>
                    </tr>
                    <tr>
                        <th>Departure Date</th>
                        <td>{{ $segment->departure_at ? $segment->departure_at->format('Y-m-d') : '' }}</td>
                        <th>Departure Time</th>
                        <td>{{ $segment->departure_at ? $segment->departure_at->format('H:i') : '' }}</td>
                    </tr>
                    <tr>
                        <th>Arrival Date</th>
                        <td>{{ $segment->arrival_at ? $segment->arrival_at->format('Y-m-d') : '' }}</td>
                        <th>Arrival Time</th>
                        <td>{{ $segment->arrival_at ? $segment->arrival_at->format('H:i') : '' }}</td>
                    </tr>
                    <tr>
                        <th>Flight Number</th>
                        <td>{{ $segment->flight_number ?? '' }}</td>
                    </tr>
                </table>
            @endforeach
        @endif

        <h3 class="section-title">Passenger Details</h3>
        @if($ticket->passengers->count() > 0)
            @foreach($ticket->passengers as $passenger)
                <table>
                    <tr>
                        <th>Passenger Name</th>
                        <td>{{ strtoupper(trim(($passenger->first_name ?? '') . ' ' . ($passenger->last_name ?? ''))) }}</td>
                        <th>Nationality</th>
                        <td>{{ $passenger->nationality ?? '' }}</td>
                    </tr>
                    <tr>
                        <th>Passport Number</th>
                        <td>{{ $passenger->passport_number ?? '' }}</td>
                        <th>Passport Expiry</th>
                        <td>{{ $passenger->passport_expiry_date ?? '' }}</td>
                    </tr>
                    <tr>
                        <th>Date of Birth</th>
                        <td>{{ $passenger->date_of_birth ?? '' }}</td>
                        <th>Phone</th>
                        <td>{{ $passenger->phone ?? '' }}</td>
                    </tr>
                </table>
            @endforeach
        @endif

        <h3 class="section-title">Price Details</h3>
        <table>
            <tr>
                <th>Base Price</th>
                <td>{{ number_format($ticket->cost_base_amount ?? 0, 0) }} {{ $ticket->currency->symbol ?? 'SAR' }}</td>
                <th>Taxes</th>
                <td>{{ number_format(($ticket->cost_tax_amount ?? 0) + ($ticket->extra_tax_amount ?? 0), 0) }} {{ $ticket->currency->symbol ?? 'SAR' }}</td>
                <th>Total Price</th>
                <td>{{ number_format($ticket->sale_total_amount ?? 0, 2) }} {{ $ticket->currency->symbol ?? 'SAR' }}</td>
            </tr>
        </table>

        <h3 class="section-title">Baggage & Restrictions</h3>
        <table>
            <tr>
                <th>Baggage Allowance</th>
                <td>N/A</td>
            </tr>
            <tr>
                <th>Restrictions</th>
                <td>N/A</td>
            </tr>
        </table>

        <h3 class="section-title">Other Information</h3>
        <table>
            <tr>
                <th>Booking Date</th>
                <td>{{ $ticket->booking_date ? $ticket->booking_date->format('Y-m-d') : ($ticket->issue_date ? $ticket->issue_date->format('Y-m-d') : '') }}</td>
                <th>Payment Method</th>
                <td>CASH</td>
                <th>Supplier</th>
                <td>{{ $ticket->supplier->name ?? 'N/A' }}</td>
            </tr>
        </table>
        <div class="break_page"></div>
    @endforeach

    <h3 class="section-title">Invoice Details</h3>
    <table>
        <tr>
            <th>Invoice Number</th>
            <td>{{ $invoice->invoice_number ?? '' }}</td>
            <th>Invoice Date</th>
            <td>{{ $invoice->created_at ? $invoice->created_at->format('Y-m-d') : '' }}</td>
            <th>Due Date</th>
            <td>{{ $invoice->due_date ? $invoice->due_date/*->format('Y-m-d')*/ : '' }}</td>
        </tr>


        <tr>
            <th>Total Taxes</th>
            <td colspan="2">{{ number_format($invoice->total_taxes ?? 0, 2) }}</td>
            <th>Total Amount</th>
            <td colspan="2">{{ number_format($invoice->total_amount ?? 0, 2) }}</td>
        </tr>
    </table>

</div>

</body>
</html>
