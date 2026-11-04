<p>Hello {{ $booking->name }},</p>

<p>
    Thank you for choosing {{ $packageName }}. Your travel quotation is attached as a PDF.
    It includes the {{ $days }}-day itinerary and the total price for your booking.
</p>

<p>
    Booking reference: #{{ $booking->id }}<br>
    Total quotation: Rs. {{ number_format((float) $booking->amount, 2) }}
</p>

<p>Please contact us if you have any questions about your trip.</p>

<p>Regards,<br>{{ config('app.name') }}</p>
