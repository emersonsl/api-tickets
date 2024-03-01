<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\Payment;
use App\Models\WebHook;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;

class WebHookController extends Controller
{
    use HttpResponses;

    public function create(Request $request){
        $validator = Validator::make($request->all(), [
            'external_id' => 'required|numeric',
        ]);
        $external_id = $request->get('external_id');

        if($validator->fails()){
            return $this->error('Invalid data', 422, $validator->errors()); 
        }

        $payment = Payment::find($external_id);
        if(!$payment){
            return $this->error('Payment not found', 404, [], $request->all());
        }

        $payment->update([
            'status' => $request->get('status'),
            'paid_at' => $request->get('paid_at'),
        ]);
        WebHook::create([
            'model_id' => $external_id,
            'model' => 'payment',
            'data' =>json_encode($request->all())
        ]);

        return $this->success('Webhook created with success', 200, ['payment' => $payment]);
    }
}
