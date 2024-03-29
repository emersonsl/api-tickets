<?php

namespace App\Mail;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentProcessedMail extends Mailable
{
    use Queueable, SerializesModels;

    protected Payment $payment;
    protected User $user;

    /**
     * Create a new message instance.
     */
    public function __construct(Payment $payment, User $user)
    {
        $this->payment = $payment;
        $this->user = $user;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Payment Processed',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mails.paymentprocessed',
            with: [
                'userName' => $this->user->name,
                'paymentAmount' => number_format(
                    num: floatval($this->payment->amount) / 100,
                    decimals: 2,
                    decimal_separator: ',',
                    thousands_separator: '.'
                ),
                'paymentKey' => $this->payment->payment
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
