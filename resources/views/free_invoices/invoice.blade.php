<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>فاتورة حرة</title>
    <style>
        @page { size: A4 portrait; margin: 1.5cm; }
        body { font-family: Arial, sans-serif; color: #000; }
        .container { width: 100%; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .logo { max-height: 70px; }
        h1 { margin: 0; font-size: 24px; }
        h3.section-title { background: #f2f2f2; padding: 6px; font-size: 14px; margin-top: 16px; border-left: 4px solid #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #333; padding: 6px; font-size: 13px; text-align: left; }
        th { background: #e0e0e0; }
        .print-button { position: fixed; top: 16px; right: 24px; padding: 10px 18px; font-size: 14px; background: #4CAF50; color: #fff; border: none; cursor: pointer; z-index: 1000; }
        @media print { .print-button { display: none; } }
    </style>
</head>
<body>
<button class="print-button" onclick="window.print()">🖨️ طباعة</button>
<div class="container">
    <div class="header">
        <img src="{{ asset('logo.png') }}" alt="Logo" class="logo" width="100">
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

        $isOther = ($invoice->free_invoiceable_type === 'other');
        $linked = $isOther ? null : $invoice->freeInvoiceable; // Only access relation when not 'other'

        $displayName = $isOther ? ($invoice->beneficiary_name ?? '-') : (($linked->company_name ?? $linked->name ?? '-') ?? '-');
        $displayAddress = $isOther ? ($invoice->beneficiary_address ?? '-') : ($linked->address ?? '-');
        $displayTax = $isOther ? ($invoice->beneficiary_tax_number ?? '-') : ($linked->tax_number ?? '-');
        $displayPhone = $isOther ? ($invoice->beneficiary_phone ?? '-') : optional(optional($linked)->contactInfos->first() ?? null)->phone;
        $displayEmail = $isOther ? ($invoice->beneficiary_email ?? '-') : optional(optional($linked)->contactInfos->first() ?? null)->email;
    @endphp

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

    <h3 class="section-title">Beneficiary Details</h3>
    <table>
        <tr>
            <th>Name</th>
            <td>{{ $displayName }}</td>
            <th>Address</th>
            <td>{{ $displayAddress }}</td>
        </tr>
        <tr>
            <th>Tax Number</th>
            <td>{{ $displayTax }}</td>
            <th>Phone</th>
            <td>{{ $displayPhone }}</td>
        </tr>
        <tr>
            <th>Email</th>
            <td colspan="3">{{ $displayEmail }}</td>
        </tr>
    </table>

    <h3 class="section-title">Invoice Items</h3>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Item</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @php $items = (array) ($invoice->items ?? []); @endphp
            @forelse($items as $idx => $item)
                @php
                    $qty = (float)($item['quantity'] ?? 0);
                    $price = (float)($item['price'] ?? 0);
                    $rowTotal = $qty * $price;
                @endphp
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item['name'] ?? '-' }}</td>
                    <td>{{ number_format($qty, 0) }}</td>
                    <td>{{ number_format($price, 2) }}</td>
                    <td>{{ number_format($rowTotal, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align:center">No items</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4" style="text-align:right">Invoice Total</th>
                <th>{{ number_format((float)$invoice->total, 2) }}</th>
            </tr>
        </tfoot>
    </table>

    <h3 class="section-title">Dates</h3>
    <table>
        <tr>
            <th>Issue Date</th>
            <td>{{ optional($invoice->issue_date)->format('Y-m-d') }}</td>
            <th>Due Date</th>
            <td>{{ optional($invoice->due_date)->format('Y-m-d') }}</td>
        </tr>
    </table>
</div>
</body>
</html>
