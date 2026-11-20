<?php

namespace App\Mail;

use App\Models\CustomPackageRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CustomPackageRequestUpdateMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public CustomPackageRequest $customPackageRequest) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Update to your custom package: '.$this->customPackageRequest->name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.custom-package-request-update',
            with: ['customPackageRequest' => $this->customPackageRequest],
        );
    }
}
