<?php

namespace App\Http\Controllers\Api\V1;

use App\Constants;
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
            'external_id' => 'required',
        ]);
        
        if($validator->fails()){
            return $this->error('Invalid data', 422, $validator->errors()); 
        }
        
        $external_id = $request->get('external_id');
        
        $payment = Payment::find($external_id);
        if(!$payment){
            return $this->error('Payment not found', 404, [], $request->all());
        }

        if($payment->hash != $request->get('hash')){
            return $this->error('Inconsistent hash', 422, [], $request->all()); 
        }

        $requestAmount = $request->get('amount');
        $paymentAmount = $payment->amount;
        $status = $this->getPaymentStatus($paymentAmount, $requestAmount);

        $payment->update([
            'status' => $status,
            'paid_at' => $request->get('paid_at'),
        ]);
        WebHook::create([
            'model_id' => $external_id,
            'model' => 'payment',
            'data' =>json_encode($request->all())
        ]);

        return $this->success('Webhook created with success', 200, ['payment' => $payment]);
    }

    private function getPaymentStatus(int $paymentAmount, int $requestAmount): int{
        if($paymentAmount == $requestAmount){
            return Constants::PAYMENT_STATUS_PAID;
        }else if($paymentAmount > $requestAmount){
            return Constants::PAYMENT_STATUS_PAID_LOWER;
        }else{
            return Constants::PAYMENT_STATUS_PAID_OVER;
        }
    }
}
