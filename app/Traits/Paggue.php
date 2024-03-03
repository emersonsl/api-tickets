<?php

namespace App\Traits;

use App\Models\Payment;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Http;

define('PAGGUE_CLIENT_KEY', env('PAGGUE_CLIENT_KEY'));
define('PAGGUE_CLIENT_SECRET', env('PAGGUE_CLIENT_SECRET'));
define('PAGGUE_SIGNATURE', env('PAGGUE_SIGNATURE'));
define('PAGGUE_END_POINT_AUTH', env('PAGGUE_END_POINT_AUTH'));
define('PAGGUE_END_POINT_BILLING', env('PAGGUE_END_POINT_BILLING'));

trait Paggue
{

    private function authenticate(){
        $data = [
            'client_key' => PAGGUE_CLIENT_KEY,
            'client_secret' => PAGGUE_CLIENT_SECRET
        ];
        $response = Http::post(PAGGUE_END_POINT_AUTH, $data);

        return $response->json();
    }
    
    private function sendRequestPix(string $user_name, float $amount, int $external_id, string $description, string $token, string $company_id){
        $response = Http::withToken($token)->withHeaders([
            'X-Company-ID' => $company_id,
            'Signature' => PAGGUE_SIGNATURE
        ])->post(PAGGUE_END_POINT_BILLING, [
            'payer_name' => $user_name,
            'amount' => $amount,
            'external_id' => $external_id,
            'description' => $description
        ]);

        return $response->json();
    }

    public function createPix(Ticket $ticket, User $user, Payment $payment){
        $responseBodyAuth = $this->authenticate();

        if(isset($responseBodyAuth['error']) && $responseBodyAuth['error']){
            return ['success' => false, 'error' => 'Error in connect API payment: ' . $responseBodyAuth['message'][0]['error'][0]];
        }

        $token = $responseBodyAuth['access_token'];
        $company_id = $responseBodyAuth['user']['companies'][0]['id'];

        $responseBodyCreatePix = $this->sendRequestPix($user->name, $ticket->amount, $payment->id, $payment->description, $token, $company_id);

        if(isset($responseBodyCreatePix['error']) && $responseBodyCreatePix['error']){
            return ['success' => false, 'error' => 'Error in connect API payment: ' . $responseBodyCreatePix['message'][0]['error'][0]];
        }

        return ['success' => true, 'data' => $responseBodyCreatePix];
    }

    
}