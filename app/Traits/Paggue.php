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
            'access_token' => "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiI3IiwianRpIjoiYWVkZDA2N2ExZmY3ZDQ0NjdkNmZhMjJiMDdiMjdkMzNhYTA0ZmU2MjM4ZjliM2Y3ZTUwYmI2ODQzNjM3ZmY4NTFhOTFkMjEwZTIyOGQ1MDMiLCJpYXQiOjE3MDkyNjg5ODUuMTQ5NTM2LCJuYmYiOjE3MDkyNjg5ODUuMTQ5NTM4LCJleHAiOjE3MDkyNzk3ODUuMTIyODM4LCJzdWIiOiIxNTQ1OCIsInNjb3BlcyI6W119.Bbo8GtdbClq6knb42mZxD0JvEaFpv-fECDWVcVwefn0NtTUjm-X_0CR4QrTuD5_owexL_7HMxrZiheqRJfRqEwdG_zp0-8N3po4K566m_xQ3gcnSUjhRinD9kRuZTsMbg5aEaTHBN_fcpQHpem7IhTXLyfl6PLxoMzIJ0inlAgQpfkYllz1dcJgglNPmKOBerxsCaGY2ZPJdqLSQBxf71kAPw2ZrI8eTd1ghVFN8E2h94vE4qRQRLI43bwSEUfh14AZB_UvCjNKOBe-m06O8bzHExT6-cNRFJD6TU8aAO-gNgmp9BeGwW95eI_MLXdgki10rEtsBZ_A6MNOe0slyeWiP5SHgL3x1Cw5DU1qmLFjpUf7ptrQdS7-bGFt3J4fu1mXoCYLWoZlrCCHru9uV_mxsihurknTLb9gEIx3wyGQucfUt4iexGF7j0OiOqi68GUyhgzAd1s71pgU1kDA79fXpG7TnxRVnCd2M8uwqM3RCsB06It4cIaV0WzwVBeM88-5iWeqLlHEf6oJIhwZ7drc3C55MipiG9vRyV3rbKCj15pUFaDJ7XqOuk4QAoTp7Ndwu2j1qZMxE6jLjbjixCSZqRIZiXzAa9oj7Ykf-6t1sYAB1JNDAl9iJ-Ru2YGJtFx3Fi-7DOvEl0-VRet6_DM3Adbj1FZmzDgc84iUGx2o",
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