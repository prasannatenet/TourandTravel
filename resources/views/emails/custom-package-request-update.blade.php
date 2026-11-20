<h1>{{ config('app.name') }} custom package update</h1>

<p>Hello {{ $customPackageRequest->booking_details['customer_name'] ?? 'traveler' }},</p>

<p>The admin has updated your custom package request.</p>

<h2>{{ $customPackageRequest->name }}</h2>
<p>{{ $customPackageRequest->departure_city }} · {{ $customPackageRequest->days }} days / {{ $customPackageRequest->nights }} nights</p>

<p><strong>Status:</strong> {{ ucfirst($customPackageRequest->status) }}</p>

@if($customPackageRequest->admin_quote !== null)
    <p><strong>Package quote:</strong> ₹{{ number_format((float) $customPackageRequest->admin_quote, 2) }}</p>
@endif

@if($customPackageRequest->admin_cost_notes)
    <h3>Cost details</h3>
    <p>{{ $customPackageRequest->admin_cost_notes }}</p>
@endif

@if($customPackageRequest->admin_response)
    <h3>Message from our team</h3>
    <p>{{ $customPackageRequest->admin_response }}</p>
@endif

<h3>Updated itinerary</h3>
@foreach($customPackageRequest->itinerary ?? [] as $day)
    <p><strong>Day {{ $day['day_number'] }}{{ !empty($day['title']) ? ' · '.$day['title'] : '' }}</strong></p>
    @if(!empty($day['description']))<p>{{ $day['description'] }}</p>@endif
    <ul>
        @foreach($day['activities'] ?? ($day['places'] ?? []) as $activity)
            <li>{{ $activity['name'] }}{{ !empty($activity['duration_hours']) ? ' · '.$activity['duration_hours'].' hours' : '' }}</li>
        @endforeach
    </ul>
@endforeach

<p>Regards,<br>{{ config('app.name') }}</p>
