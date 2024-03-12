<?php

namespace App\Jobs;

use App\Http\Controllers\Api\V1\UserController;
use App\Mail\PaymentProcessedMail;
use App\Mail\PaymentProcessedAdminMail;
use App\Models\Payment;
use App\Models\Ticket;
use App\Models\User;
use App\Traits\Paggue;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class ProcessPayment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Paggue;

    protected Payment $payment;
    protected User $user;
    protected Ticket $ticket;
    /**
     * Create a new job instance.
     */
    public function __construct(Payment $payment, User $user, Ticket $ticket)
    {
        $this->payment = $payment;
        $this->user = $user;
        $this->ticket = $ticket;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $result = $this->createPix($this->ticket, $this->user, $this->payment);

        $this->payment->update([
            'hash' => $result['data']['hash'],
            'paid_at' => $result['data']['paid_at'] ?: null,
            'expiration_at' => $result['data']['expiration_at'] ?: null,
            'payment' => $result['data']['payment'],
            'status' => $result['data']['status'],
            'reference_id' => $result['data']['reference']
        ]);

        Mail::to($this->user)->queue(new PaymentProcessedMail($this->payment, $this->user));
        
        $admin = UserController::getFirstAdmin();

        Mail::to($admin)->queue(new PaymentProcessedAdminMail($this->payment, $this->user, $admin));
    }
}
