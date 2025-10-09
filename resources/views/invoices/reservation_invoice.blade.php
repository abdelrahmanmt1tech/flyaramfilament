<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>Reservation Invoice</title>
    <style>
        @page { size: A4 portrait; margin: 1.5cm; }
        body { font-family: Arial, sans-serif; margin: 0; background: #fff; color: #000; }
        .container { padding: 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .logo { max-height: 70px; }
        h1 { font-size: 24px; margin: 0; }
        h3.section-title { background: #f2f2f2; padding: 6px 8px; font-size: 14px; margin: 16px 0 6px; border-left: 4px solid #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        th, td { border: 1px solid #333; padding: 6px; font-size: 13px; }
        th { background: #eaeaea; text-align: left; }
        .print-button { position: fixed; top: 16px; right: 24px; padding: 10px 18px; font-size: 14px; background: #4CAF50; color: #fff; border: none; cursor: pointer; z-index: 1000; }
        @media print { .print-button { display: none; } body, .container { margin: 0; padding: 0; } }
        .totals { margin-top: 12px; width: 40%; margin-left: auto; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
<button class="print-button" onclick="window.print()">🖨️ طباعة</button>
<div class="container">
    <div class="header">
        <img src="{{ asset('logo.png') }}" alt="Logo" class="logo" width="100">
    </div>

    @if(($invoice->type ?? null) === 'refund')
        <h2 style="background:#f2f2f2;border:2px dashed #d33;color:#d33;text-align:center;padding:8px;margin-top:0;">
            فاتورة استرجاع / REFUND INVOICE
        </h2>
    @endif

    @php
        $companyKeys = [
            'company_name_en', 'company_address_en', 'tax_number', 'commercial_register', 'tourism_license',
        ];
        $company = \App\Models\Setting::whereIn('key', $companyKeys)->pluck('value', 'key');

        // Related entity from reservation morph
        $related = $reservation?->related;
        $relatedName = $related->company_name ?? $related->name ?? '';

        // Passenger on reservation
        $passengerName = $reservation?->passenger?->first_name;

        // Items info
        $items = $reservation?->items()->with('supplier')->get();

        // Service type: prefer item's service_type; fallback to mapped Arabic by reservation_type or raw
        $reservationTypes = [
            'hotel' => 'فندق',
            'car' => 'سيارة',
            'tourism' => 'سياحة',
            'visa' => 'تأشيرات',
            'international_license' => 'رخصة قيادة دولية',
            'train' => 'حجز قطار',
            'meeting_room' => 'حجز قاعة إجتماعات',
            'internal_transport' => 'تنقلات داخلية ',
            'other' => 'أخرى',
        ];
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

    <h3 class="section-title">Invoice Details</h3>
    <table>
        <tr>
            <th>Invoice No</th>
            <td>{{ $invoice->invoice_number }}</td>
            <th>Date</th>
            <td>{{ optional($invoice->created_at)->format('Y-m-d') }}</td>
        </tr>
        <tr>
            <th>Reservation No</th>
            <td>{{ $reservation?->reservation_number }}</td>
            <th>Due Date</th>
            <td>{{ optional($invoice->due_date)->format('Y-m-d') }}</td>
        </tr>
    </table>

    <h3 class="section-title">Client Details</h3>
    <table>
        <tr>
            <th>Client Name</th>
            <td>{{ $relatedName }}</td>
            <th>Passenger</th>
            <td>{{ $passengerName ?? '-' }}</td>
        </tr>
    </table>

    <h3 class="section-title">Reservation Items</h3>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Service Type</th>
                <th>Supplier</th>
                <th>Details</th>
                <th class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @php $row = 1; @endphp
            @forelse($items as $item)
                @php
                    $serviceType = $item->service_type
                        ?: ($reservationTypes[$item->reservation_type] ?? $item->reservation_type);
                    $supplierName = $item->supplier->name ?? '-';
                @endphp
                <tr>
                    <td>{{ $row++ }}</td>
                    <td>{{ $serviceType }}</td>
                    <td>{{ $supplierName }}</td>
                    <td>
                        @if($item->isHotel())
                            {{ $item->hotel_name }} / {{ $item->room_type }} / {{ $item->nights_count }} nights
                        @else
                            {{ $item->service_details ?? '-' }}
                        @endif
                    </td>
                    <td class="text-right">{{ number_format((float)$item->total_amount, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">No items</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <th>Total</th>
            <td class="text-right">{{ number_format((float)$invoice->total_amount, 2) }}</td>
        </tr>
    </table>

    @if(!empty($invoice->notes))
        <h3 class="section-title">Notes</h3>
        <table>
            <tr>
                <td>{{ $invoice->notes }}</td>
            </tr>
        </table>
    @endif
</div>
</body>
</html>
