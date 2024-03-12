<?php

namespace App\Mail;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentProcessedAdminMail extends Mailable
{
    use Queueable, SerializesModels;

    protected Payment $payment;
    protected User $user;
    protected User $admin;

    /**
     * Create a new message instance.
     */
    public function __construct(Payment $payment, User $user, User $admin)
    {
        $this->payment = $payment;
        $this->user = $user;
        $this->admin = $admin;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Process Payment Admin Mail',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mails.paymentprocessedadmin',
            with: [
                'clientName' => $this->user->name,
                'adminName' => $this->admin->name,
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
