<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فاتورة ضريبية مبسطة</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            padding: 20px;
        }

        .invoice-container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #eee;
            position: relative;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .logo-text {
            font-size: 32px;
            font-weight: bold;
        }

        .fly {
            color: #ff6b35;
        }

        .aram {
            color: #2c3e50;
        }

        .header-title {
            text-align: left;
        }

        .header-title h1 {
            font-size: 18px;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .header-title p {
            font-size: 14px;
            color: #7f8c8d;
        }

        .qr-code {
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            text-align: center;
        }

        .qr-code svg {
            width: 80px;
            height: 80px;
        }

        .invoice-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        .info-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            color: #6c757d;
            font-size: 13px;
        }

        .info-value {
            color: #2c3e50;
            font-weight: 500;
            font-size: 13px;
        }

        .parties-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        .party-box {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
        }

        .party-title {
            background: #34495e;
            color: white;
            padding: 10px 15px;
            margin: -20px -20px 15px -20px;
            border-radius: 7px 7px 0 0;
            font-size: 14px;
            font-weight: 600;
        }

        .party-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            font-size: 13px;
        }

        .party-label {
            color: #6c757d;
        }

        .party-value {
            color: #2c3e50;
            font-weight: 500;
            text-align: left;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            overflow: hidden;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .items-table thead {
            background: #34495e;
            color: white;
        }

        .items-table th {
            padding: 12px;
            text-align: center;
            font-size: 13px;
            font-weight: 600;
        }

        .items-table td {
            padding: 15px 12px;
            text-align: center;
            border-bottom: 1px solid #e9ecef;
            font-size: 12px;
            color: #2c3e50;
        }

        .items-table tbody tr:hover {
            background-color: #f8f9fa;
        }

        .items-table tbody tr:last-child td {
            border-bottom: none;
        }

        .item-description {
            text-align: right;
            line-height: 1.6;
        }

        .item-title {
            font-weight: 600;
            margin-bottom: 5px;
            color: #2c3e50;
        }

        .item-details {
            font-size: 11px;
            color: #6c757d;
        }

        .totals-section {
            max-width: 400px;
            margin-right: auto;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e9ecef;
        }

        .total-row:last-child {
            border-bottom: none;
            font-size: 16px;
            font-weight: bold;
            color: #2c3e50;
            padding-top: 15px;
            border-top: 2px solid #34495e;
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
            border-radius: 5px;
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }

            .invoice-container {
                box-shadow: none;
                padding: 20px;
            }

            .print-button {
                display: none;
            }
        }

        @media (max-width: 768px) {

            .invoice-info,
            .parties-section {
                grid-template-columns: 1fr;
            }
        }

        .invoice-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            /* Two equal columns */
            gap: 20px;
            /* Reduced gap for better alignment */
            margin-bottom: 30px;
        }

        .info-section,
        .payment-currency-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            color: #6c757d;
            font-size: 13px;
        }

        .info-value {
            color: #2c3e50;
            font-weight: 500;
            font-size: 13px;
        }

        @media (max-width: 768px) {
            .invoice-info {
                grid-template-columns: 1fr;
                /* Stack vertically on mobile */
            }
        }
    </style>
</head>

<body>

    <button class="print-button" onclick="window.print()">🖨️ طباعة</button>

    <div class="invoice-container">
        <div class="header">
            <div class="logo">
                <img src="{{ asset('logo.png') }}" alt="Logo" class="logo" width="100">
                {{-- <h1>تذكرة سفر / Ticket Invoice</h1> --}}

            </div>

            <div class="qr-code">
                {!! $qrCode !!}
                <div style="font-size: 10px; margin-top: 5px;">Scan to verify</div>
            </div>
        </div>

        @php
            $companyKeys = [
                'company_name_en',
                'company_name_ar',
                'company_address_en',
                'company_address_ar',
                'tax_number',
                'commercial_register',
                'tourism_license',
                'building_no',
                'street',
                'district',
                'city',
                'postal_code',
                'additional_no',
            ];
            $company = \App\Models\Setting::whereIn('key', $companyKeys)->pluck('value', 'key');
        @endphp
        <div class="invoice-info">
            <div class="info-section">
                <div class="info-row">
                    <span class="info-label">Invoice Number:</span>
                    <span class="info-value">{{ $invoice->invoice_number }}</span>
                    <span class="info-label">:رقم الفاتورة</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Issue Time:</span>
                    <span class="info-value">{{ $invoice->created_at->format('Y-m-d H:i:s') }}</span>
                    <span class="info-label">:وقت الإصدار</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Supply Date:</span>
                    <span class="info-value">{{ $invoice->created_at->format('Y-m-d') }}</span>
                    <span class="info-label">:تاريخ التوريد</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Supply (Hijri):</span>
                    <span class="info-value">-</span>
                    <span class="info-label">:تاريخ التوريد (هجري)</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Due Date:</span>
                    <span
                        class="info-value">{{ $invoice->due_date ? $invoice->due_date->format('Y-m-d') : $invoice->created_at->format('Y-m-d') }}</span>
                    <span class="info-label">:تاريخ الاستحقاق</span>
                </div>
            </div>
            <div class="payment-currency-section">
                <div class="info-row">
                    <span class="info-label">حالة الدفع:</span>
                    <span class="info-value">استحقت الدفع - مسجلة</span>
                </div>
                <div class="info-row">
                    <span class="info-label">عملة الفاتورة:</span>
                    <span class="info-value">SAR</span>
                </div>
            </div>
        </div>

        @php
            $ticket = $invoice->tickets->first();
            $buyer = $ticket->client ?? ($ticket->supplier ?? ($ticket->branch ?? $ticket->franchise));
        @endphp

        <div class="parties-section">
            <div class="party-box">
                <div class="party-title">المشتري - Buyer</div>
                <div class="party-row">
                    <span class="party-label">:الاسم</span>
                    <span class="party-value">{{ $buyer->company_name ?? ($buyer->name ?? '-') }}</span>
                </div>
                <div class="party-row">
                    <span class="party-label">:رقم المبنى</span>
                    <span class="party-value">-</span>
                </div>
                <div class="party-row">
                    <span class="party-label">:العنوان (الشارع)</span>
                    <span class="party-value">{{ $buyer->address ?? ($buyer->contactInfo->address ?? '-') }}</span>
                </div>
                <div class="party-row">
                    <span class="party-label">:المنطقة</span>
                    <span class="party-value">-</span>
                </div>
                <div class="party-row">
                    <span class="party-label">:المدينة</span>
                    <span class="party-value">-</span>
                </div>
                <div class="party-row">
                    <span class="party-label">:الدولة</span>
                    <span class="party-value">SA - المملكة العربية السعودية</span>
                </div>
                <div class="party-row">
                    <span class="party-label">:الرمز البريدي</span>
                    <span class="party-value">-</span>
                </div>
                <div class="party-row">
                    <span class="party-label">:الرقم الإضافي</span>
                    <span class="party-value">-</span>
                </div>
                <div class="party-row">
                    <span class="party-label">:الرقم الضريبي</span>
                    <span class="party-value">{{ $buyer->tax_number ?? '-' }}</span>
                </div>
                <div class="party-row">
                    <span class="party-label">:بطاقة تجارية</span>
                    <span class="party-value">-</span>
                </div>
            </div>

            <div class="party-box">
                <div class="party-title">البائع - Seller</div>
                <div class="party-row">
                    <span class="party-label">:الاسم</span>
                    <span
                        class="party-value">{{ $company['company_name_ar'] ?? ($company['company_name_en'] ?? '') }}</span>
                </div>
                <div class="party-row">
                    <span class="party-label">:رقم المبنى</span>
                    <span class="party-value">{{ $company['building_no'] ?? '-' }}</span>
                </div>
                <div class="party-row">
                    <span class="party-label">:العنوان (الشارع)</span>
                    <span class="party-value">{{ $company['street'] ?? ($company['company_address_ar'] ?? '-') }}</span>
                </div>
                <div class="party-row">
                    <span class="party-label">:المنطقة</span>
                    <span class="party-value">{{ $company['district'] ?? '-' }}</span>
                </div>
                <div class="party-row">
                    <span class="party-label">:المدينة</span>
                    <span class="party-value">{{ $company['city'] ?? 'جدة' }}</span>
                </div>
                <div class="party-row">
                    <span class="party-label">:الدولة</span>
                    <span class="party-value">SA - المملكة العربية السعودية</span>
                </div>
                <div class="party-row">
                    <span class="party-label">:الرمز البريدي</span>
                    <span class="party-value">{{ $company['postal_code'] ?? '-' }}</span>
                </div>
                <div class="party-row">
                    <span class="party-label">:الرقم الإضافي</span>
                    <span class="party-value">{{ $company['additional_no'] ?? '-' }}</span>
                </div>
                <div class="party-row">
                    <span class="party-label">:الرقم الضريبي</span>
                    <span class="party-value">{{ $company['tax_number'] ?? '' }}</span>
                </div>
                <div class="party-row">
                    <span class="party-label">:السجل التجاري</span>
                    <span class="party-value">{{ $company['commercial_register'] ?? '' }}</span>
                </div>
            </div>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>البند / Item</th>
                    <th>السعر / Rate</th>
                    <th>الكمية / Qty</th>
                    <th>الاجمالي بدون الضريبة</th>
                    <th>الضرائب </th>
                    <th>الضرائب الإضافية</th>
                    <th>نسبة الضريبة من الاجمالي</th>
                    <th>الإجمالي</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($tickets as $index => $ticket)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="item-description">
                            <div class="item-title">تذكرة جوال / Flight Ticket</div>
                            <div class="item-details">
                                شركة الطيران: {{ $ticket->airline->name ?? ($ticket->airline_name ?? '-') }}
                                @if ($ticket->airline)
                                    ({{ $ticket->airline->iata_code ?? '' }})
                                @endif
                                <br>
                                @if ($ticket->passengers->count() > 0)
                                    الراكب:
                                    @foreach ($ticket->passengers as $passenger)
                                        {{ $passenger->first_name }}
                                        {{ $passenger->last_name }}{{ !$loop->last ? '، ' : '' }}
                                    @endforeach
                                    <br>
                                @endif
                                رقم التذكرة:
                                {{ $ticket->ticket_number_full ?? ($ticket->ticket_number_core ?? '-') }}<br>
                                رقم الحجز (PNR): {{ $ticket->pnr ?? '-' }}<br>
                                تاريخ الإصدار:
                                {{ $ticket->issue_date ? $ticket->issue_date->format('Y-m-d') : '-' }}<br>
                                @if ($ticket->segments->count() > 0)
                                    المسار:
                                    {{ $ticket->segments->pluck('origin.iata')->join(' → ') }}
                                    →
                                    {{ $ticket->segments->last()->destination->iata }}
                                    <br>
                                    @foreach ($ticket->segments as $segment)
                                        رقم الرحلة: {{ $segment->flight_number ?? '-' }}<br>
                                        المغادرة:
                                        {{ $segment->departure_at ? $segment->departure_at->format('Y-m-d H:i') : '-' }}<br>
                                    @endforeach
                                @else
                                    المسار: {{ $ticket->itinerary_string ?? '-' }}<br>
                                @endif
                            </div>
                        </td>
                        <td>{{ number_format($ticket->cost_base_amount ?? 0, 2) }}</td>
                        <td>1</td>
                        <td>{{ number_format($ticket->cost_base_amount ?? 0, 2) }}</td>
                        {{-- <td>{{ $ticket->taxType->value ?? '0' }}%</td> --}}
                        <td>{{ number_format($ticket->cost_tax_amount ?? 0, 2) }}</td>
                        <td>{{ number_format($ticket->extra_tax_amount ?? 0, 2) }}</td>
                        @php
                            $taxes = ($ticket->cost_tax_amount ?? 0) + ($ticket->extra_tax_amount ?? 0);
                            $saleAmount = $ticket->sale_total_amount ?? 0;
                            $percentage = $saleAmount > 0 ? ($taxes / $saleAmount) * 100 : 0;
                        @endphp

                        <td>{{ number_format($percentage, 2) }}%</td>
                        <td>{{ number_format($ticket->sale_total_amount ?? 0, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @php
            $subtotal = $tickets->sum('cost_base_amount');
            $totalTaxes = $tickets->sum(function ($t) {
                return ($t->cost_tax_amount ?? 0) + ($t->extra_tax_amount ?? 0);
            });
            $totalAmount = $tickets->sum('sale_total_amount');
            $currency = $tickets->first()->currency->code ?? 'SAR';
        @endphp

        <div class="totals-section">
            <div class="total-row">
                <span>المبلغ الإجمالي (Subtotal):</span>
                <span>{{ number_format($subtotal, 2) }} {{ $currency }}</span>
            </div>
            <div class="total-row">
                <span>الضريبة (Tax):</span>
                <span>{{ number_format($totalTaxes, 2) }} {{ $currency }}</span>
            </div>
            <div class="total-row">
                <span>الإجمالي النهائي (Total):</span>
                <span>{{ number_format($totalAmount, 2) }} {{ $currency }}</span>
            </div>
        </div>
    </div>
</body>

</html>
