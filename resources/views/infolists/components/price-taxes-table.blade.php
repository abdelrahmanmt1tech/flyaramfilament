<style>
    .price-taxes-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    font-size: 0.875rem; /* text-sm */
    border: 1px solid #e5e7eb; /* gray-200 */
    border-radius: 0.5rem; /* rounded-lg */
}

.price-taxes-table th {
    background-color: #f9fafb; /* gray-100 */
    text-align: center;
    padding: 0.5rem 1rem; /* py-2 px-4 */
    border-bottom: 1px solid #d1d5db; /* gray-300 */
    font-weight: 600;
}

.price-taxes-table td {
    padding: 0.5rem 1rem; /* py-2 px-4 */
    border-bottom: 1px solid #e5e7eb; /* gray-200 */
    text-align: center;

}
h1{
    font-size: 1rem;
}
</style>

<h1>{{ __('dashboard.fields.price_taxes_breakdown') }}:</h1>
<br>
<table class="price-taxes-table">
    <thead>
        <tr>
            <th>{{ __('dashboard.fields.code') }}</th>
            <th>{{ __('dashboard.fields.amount') }}</th>
            <th>{{ __('dashboard.fields.currency') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($getState() ?? [] as $item)
            <tr>
                <td>{{ $item['code'] ?? '-' }}</td>
                <td>{{ $item['amount'] ?? '-' }}</td>
                <td>{{ $item['currency'] ?? '-' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
