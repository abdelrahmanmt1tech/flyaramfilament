<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فاتورة حرة</title>
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
            font-size: 13px;
            direction: rtl;
        }

        .invoice-container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 25px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #eee;
            position: relative;
        }

        .logo {
            width: 100px;
        }

        .header-title {
            text-align: left;
        }

        .header-title h1 {
            font-size: 22px;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .header-title p {
            font-size: 14px;
            color: #7f8c8d;
        }

        .invoice-type-badge {
            position: absolute;
            top: 10px;
            left: 50%;
            transform: translateX(-50%);
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 14px;
            background: linear-gradient(135deg, #9C27B0, #7B1FA2);
            color: white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .qr-code {
            position: absolute;
            top: 0;
            left: 10px;
            text-align: center;
        }

        .qr-code svg {
            width: 80px;
            height: 80px;
        }

        .invoice-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }

        .info-section {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            border-bottom: 1px solid #e9ecef;
            font-size: 13px;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            color: #6c757d;
        }

        .info-value {
            color: #2c3e50;
            font-weight: 500;
        }

        .parties-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }

        .party-box {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
        }

        .party-title {
            background: #34495e;
            color: white;
            padding: 8px 12px;
            margin: -15px -15px 12px -15px;
            border-radius: 7px 7px 0 0;
            font-size: 13px;
            font-weight: 600;
        }

        .party-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            font-size: 12px;
        }

        .party-label {
            color: #6c757d;
        }

        .party-value {
            color: #2c3e50355;
            font-weight: 500;
            text-align: left;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            overflow: hidden;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .items-table thead {
            background: #34495e;
            color: white;
        }

        .items-table th {
            padding: 10px 8px;
            text-align: center;
            font-size: 12px;
            font-weight: 600;
        }

        .items-table td {
            padding: 12px 8px;
            text-align: center;
            border-bottom: 1px solid #e9ecef;
            font-size: 11px;
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
            line-height: 1.5;
        }

        .totals-section {
            max-width: 400px;
            margin-right: auto;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
            font-size: 13px;
        }

        .total-row:last-child {
            border-bottom: none;
            font-size: 15px;
            font-weight: bold;
            color: #2c3e50;
            padding-top: 12px;
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
                margin: 0;
            }

            .invoice-container {
                box-shadow: none;
                padding: 25px;
                max-width: 100%;
                page-break-inside: avoid;
            }

            .print-button {
                display: none;
            }

            .header,
            .invoice-info,
            .parties-section,
            .items-table,
            .totals-section {
                margin-bottom: 15px;
            }

            .items-table th,
            .items-table td {
                padding: 8px 6px;
                font-size: 10px;
            }

            .qr-code svg {
                width: 40px;
                height: 40px;
            }

            .invoice-type-badge {
                padding: 6px 15px;
                font-size: 12px;
            }

            @page {
                size: A4;
                margin: 10mm;
            }
        }

        @media (max-width: 768px) {

            .invoice-info,
            .parties-section {
                grid-template-columns: 1fr;
            }

            .print-button {
                right: 15px;
                top: 15px;
                padding: 10px 16px;
                font-size: 14px;
            }
        }
    </style>
</head>

<body>

    <button class="print-button" onclick="window.print()">🖨️ طباعة</button>

    <div class="invoice-container">
        <div class="header">
            <div class="logo">
                <img src="{{ asset('logo.png') }}" alt="Logo" width="100">
            </div>

            <div class="invoice-type-badge">فاتورة حرة</div>

            <div class="qr-code">
                {!! $qrCode ??
                    '<svg viewBox="0 0 100 100"><rect width="100" height="100" fill="#eee"/><text x="50" y="55" font-size="12" text-anchor="middle" fill="#999">QR</text></svg>' !!}
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
            ];
            $company = \App\Models\Setting::whereIn('key', $companyKeys)->pluck('value', 'key');

            $isOther = $invoice->free_invoiceable_type === 'other';
            $linked = $isOther ? null : $invoice->freeInvoiceable;

            $displayName = $isOther
                ? $invoice->beneficiary_name ?? '-'
                : $linked->company_name ?? ($linked->name ?? '-');
            $displayAddress = $isOther ? $invoice->beneficiary_address ?? '-' : $linked->address ?? '-';
            $displayTax = $isOther ? $invoice->beneficiary_tax_number ?? '-' : $linked->tax_number ?? '-';
            $displayPhone = $isOther
                ? $invoice->beneficiary_phone ?? '-'
                : optional(optional($linked)->contactInfos->first())->phone ?? '-';
            $displayEmail = $isOther
                ? $invoice->beneficiary_email ?? '-'
                : optional(optional($linked)->contactInfos->first())->email ?? '-';
        @endphp

        <div class="invoice-info">
            <div class="info-section">
                <div class="info-row">
                    <span class="info-label">رقم الفاتورة:</span>
                    <span class="info-value">{{ $invoice->invoice_number ?? $invoice->id }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">تاريخ الإصدار:</span>
                    <span
                        class="info-value">{{ optional($invoice->issue_date)->format('Y-m-d') ?? now()->format('Y-m-d') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">تاريخ الاستحقاق:</span>
                    <span class="info-value">{{ optional($invoice->due_date)->format('Y-m-d') ?? '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">حالة الدفع:</span>
                    <span class="info-value">استحقت الدفع - مسجلة</span>
                </div>
                <div class="info-row">
                    <span class="info-label">العملة:</span>
                    <span class="info-value">SAR</span>
                </div>
            </div>
        </div>

        <div class="parties-section">
            <!-- المستفيد (المشتري) -->
            <div class="party-box">
                <div class="party-title">المستفيد - Beneficiary</div>
                <div class="party-row">
                    <span class="party-label">:الاسم</span>
                    <span class="party-value">{{ $displayName }}</span>
                </div>
                <div class="party-row">
                    <span class="party-label">:العنوان</span>
                    <span class="party-value">{{ $displayAddress }}</span>
                </div>
                <div class="party-row">
                    <span class="party-label">:رقم الهاتف</span>
                    <span class="party-value">{{ $displayPhone }}</span>
                </div>
                <div class="party-row">
                    <span class="party-label">:البريد الإلكتروني</span>
                    <span class="party-value">{{ $displayEmail }}</span>
                </div>
                <div class="party-row">
                    <span class="party-label">:الرقم الضريبي</span>
                    <span class="party-value">{{ $displayTax }}</span>
                </div>
            </div>

            <!-- الشركة (البائع) -->
            <div class="party-box">
                <div class="party-title">الشركة - Company</div>
                <div class="party-row">
                    <span class="party-label">:الاسم</span>
                    <span
                        class="party-value">{{ $company['company_name_ar'] ?? ($company['company_name_en'] ?? '-') }}</span>
                </div>
                <div class="party-row">
                    <span class="party-label">:العنوان</span>
                    <span
                        class="party-value">{{ $company['company_address_ar'] ?? ($company['company_address_en'] ?? '-') }}</span>
                </div>
                <div class="party-row">
                    <span class="party-label">:الرقم الضريبي</span>
                    <span class="party-value">{{ $company['tax_number'] ?? '-' }}</span>
                </div>
                <div class="party-row">
                    <span class="party-label">:السجل التجاري</span>
                    <span class="party-value">{{ $company['commercial_register'] ?? '-' }}</span>
                </div>
                <div class="party-row">
                    <span class="party-label">:رخصة السياحة</span>
                    <span class="party-value">{{ $company['tourism_license'] ?? '-' }}</span>
                </div>
            </div>
        </div>

        <!-- جدول البنود -->
        <table class="items-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>البند</th>
                    <th>الكمية</th>
                    <th>سعر الوحدة</th>
                    <th>الإجمالي</th>
                </tr>
            </thead>
            <tbody>
                @php $items = (array) ($invoice->items ?? []); @endphp
                @forelse($items as $idx => $item)
                    @php
                        $qty = (float) ($item['quantity'] ?? 0);
                        $price = (float) ($item['price'] ?? 0);
                        $rowTotal = $qty * $price;
                    @endphp
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td class="item-description">
                            <div class="item-title">{{ $item['name'] ?? '-' }}</div>
                        </td>
                        <td>{{ number_format($qty, 0) }}</td>
                        <td>{{ number_format($price, 2) }}</td>
                        <td>{{ number_format($rowTotal, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align:center; color:#999;">لا توجد بنود</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @php
            $items = (array) ($invoice->items ?? []);
            $subtotal = collect($items)->sum(fn($i) => ($i['quantity'] ?? 0) * ($i['price'] ?? 0));
            $taxAmount = $invoice->total - $subtotal; 
            $taxName = $invoice->taxType?->name ?? 'بدون ضريبة';
        @endphp

        <div class="totals-section">
            <div class="total-row">
                <span>المبلغ قبل الضريبة:</span>
                <span>{{ number_format($subtotal, 2) }} SAR</span>
            </div>
            <div class="total-row">
                <span>الضريبة ({{ $taxName }}):</span>
                <span>{{ number_format($taxAmount, 2) }} SAR</span>
            </div>
            <div class="total-row">
                <span>الإجمالي النهائي:</span>
                <span>{{ number_format($invoice->total, 2) }} SAR</span>
            </div>
        </div>
    </div>
</body>

</html>
