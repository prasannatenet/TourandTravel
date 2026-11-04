<?php

namespace App\Mail;

use App\Models\Booking;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingQuotationMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array<int, array{day_number: int, start_time: string, title: string, nights: ?int, additional_price: float}>  $itineraryItems
     */
    public function __construct(
        public Booking $booking,
        public string $packageName,
        public int $days,
        public array $itineraryItems,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your travel quotation for '.$this->packageName,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.booking-quotation',
            with: $this->quotationData(),
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(
                fn (): string => $this->quotationPdf(),
                'booking-quotation-'.$this->booking->id.'.pdf',
            )->withMime('application/pdf'),
        ];
    }

    public function quotationPdf(): string
    {
        return Pdf::loadView('emails.booking-quotation-pdf', $this->quotationData())
            ->setPaper('a4')
            ->output();
    }

    /**
     * @return array{
     *     booking: Booking,
     *     packageName: string,
     *     days: int,
     *     itineraryItems: array<int, array{day_number: int, start_time: string, title: string, nights: ?int, additional_price: float}>
     * }
     */
    private function quotationData(): array
    {
        return [
            'booking' => $this->booking,
            'packageName' => $this->packageName,
            'days' => $this->days,
            'itineraryItems' => $this->itineraryItems,
        ];
    }
}
