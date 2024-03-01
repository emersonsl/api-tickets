<?php

namespace App\Traits;

use App\Models\Payment;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Http;

define('PAGGUE_CLIENT_KEY', env('PAGGUE_CLIENT_KEY'));
define('PAGGUE_CLIENT_SECRET', env('PAGGUE_CLIENT_SECRET'));
define('PAGGUE_END_POINT_AUTH', env('PAGGUE_END_POINT_AUTH'));
define('PAGGUE_END_POINT_BILLING', env('PAGGUE_END_POINT_BILLING'));

trait Paggue
{

    private function authenticate(){
        //by pass
        return [
            'access_token' => "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiI3IiwianRpIjoiMzZiZmM0N2I3Njc0NDUxYWI0ODNlNjFiNDc2YjY1Mzc0M2JmZWMzZmIwZjE1NWM3ZjRlZGViNDczN2IxMmFjMjE2MmZmZjIxZDMzN2JmZjYiLCJpYXQiOjE3MDkyODE5NjIuNjU5OTgxLCJuYmYiOjE3MDkyODE5NjIuNjU5OTg0LCJleHAiOjE3MDkyOTI3NjIuNjMyNTgsInN1YiI6IjE1NDU4Iiwic2NvcGVzIjpbXX0.Q7THg6_daCOma-per7NfD7DZ8Vq9vE11pKujW8lXrvQBdpSYCBqsIEhCkN0TUBo7D3g5hDB45WOHZxLN056PvE8EFi5F_RG-ET9kixb5Tl_6jlHKkEBp8Z9XHVEg4UA_edX-ZUlCm1RZegbny-ggR6xWUtsbQOqKZHntpkun_Ug1vOmv_lA3x3zvFR7XoOGZgPUnoKqVqgzp_wCXJk6_3sd-IxLpntyURXY0-FaDXU62qHqePC9glPGsBLzHqqt-1dKl855_ijnewHCtnX82as8zxMPtzC_z3ygi9ZioKBKM9bolg1gT7Hi3FQCVSqzqtXo2uUb2tW1406DT6XbwKpqnA2MKX5ig3UI6BUwc9kHnUCMBJLfeOQKXud7daZTC_9T4bSoBJ4jutL5N3q7ZXbAt9NqCDIyfoATGiFeAb1C8fE9mO70NpS8VSWxYKPc0yOVQUXgpjoFN8ARXm_px-aOtjcscxIojKtD6yRS1l_0ISwEMJlJKVDuk-A4t2JsyYj04dQscWYOUnUDj4E6ROMpSVfGIdT8KMvoprny9e30jtjQCAVCxfVUwDSkOiU3E3rvxTsb_BLEd1VYmo8JLHrCBy79fIh0mcceoJUCBX8zrAMkunJT0KQ1U5o7piEV6G2tx2ZqsAvuQ5WAlrQw24c_GNq04x-xT30Yk31AIqPU",
            'user' => [
                'companies' => [
                    0 => [
                        'id' => 150909
                    ]
                ]
            ]
        ];
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
            'Signature' => $token
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

        $amount = intval($ticket->amount * 100);

        $responseBodyCreatePix = $this->sendRequestPix($user->name, $amount, $payment->id, $payment->description, $token, $company_id);

        if(isset($responseBodyCreatePix['error']) && $responseBodyCreatePix['error']){
            return ['success' => false, 'error' => 'Error in connect API payment: ' . $responseBodyCreatePix['message'][0]['error'][0]];
        }

        return ['success' => true, 'data' => $responseBodyCreatePix];
    }

    
}