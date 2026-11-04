<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Travel quotation #{{ $booking->id }}</title>
    <style>
        body { color: #1f2937; font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h1 { color: #312e81; font-size: 22px; margin-bottom: 4px; }
        h2 { color: #312e81; font-size: 15px; margin: 24px 0 8px; }
        .muted { color: #64748b; }
        .summary { border: 1px solid #cbd5e1; margin-top: 18px; padding: 12px; }
        .summary p { margin: 5px 0; }
        table { border-collapse: collapse; margin-top: 8px; width: 100%; }
        th, td { border: 1px solid #cbd5e1; padding: 8px; text-align: left; }
        th { background: #eef2ff; }
        .price { text-align: right; white-space: nowrap; }
        .total { color: #312e81; font-size: 16px; font-weight: bold; margin-top: 18px; text-align: right; }
    </style>
</head>
<body>
    <h1>Travel quotation</h1>
    <p class="muted">Booking reference #{{ $booking->id }}</p>

    <div class="summary">
        <p><strong>Customer:</strong> {{ $booking->name }}</p>
        <p><strong>Package:</strong> {{ $packageName }}</p>
        <p><strong>Duration:</strong> {{ $days }} {{ $days === 1 ? 'day' : 'days' }}</p>
        <p><strong>Travelers:</strong> {{ $booking->adult_count }} adults, {{ $booking->child_count }} children, {{ $booking->student_count }} students</p>
        @if ($booking->check_in_date)
            <p><strong>Check-in:</strong> {{ $booking->check_in_date->format('M j, Y') }}</p>
        @endif
        @if ($booking->check_out_date)
            <p><strong>Check-out:</strong> {{ $booking->check_out_date->format('M j, Y') }}</p>
        @endif
    </div>

    <h2>Itinerary</h2>
    @if (count($itineraryItems) > 0)
        <table>
            <thead>
                <tr>
                    <th>Day</th>
                    <th>Time</th>
                    <th>Activity / accommodation</th>
                    <th>Nights</th>
                    <th class="price">Additional price</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($itineraryItems as $item)
                    <tr>
                        <td>{{ $item['day_number'] }}</td>
                        <td>{{ \Illuminate\Support\Carbon::createFromFormat('H:i', $item['start_time'])->format('g:i A') }}</td>
                        <td>{{ $item['title'] }}</td>
                        <td>{{ $item['nights'] ?? '-' }}</td>
                        <td class="price">Rs. {{ number_format($item['additional_price'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>No itinerary items have been added.</p>
    @endif

    <p class="total">Total quotation: Rs. {{ number_format((float) $booking->amount, 2) }}</p>
    <p class="muted">This quotation is for the itinerary and traveler details shown above.</p>
</body>
</html>
