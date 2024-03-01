<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Ticket;
use Illuminate\Support\Facades\Validator;
use App\Traits\HttpResponses;
use App\Traits\Paggue;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

define('STATUS_NOT_PAID', 0);
define('STATUS_PAID', 1);

class PaymentController extends Controller
{
    use HttpResponses;
    use Paggue;

    public function create(Request $request){
        $validator = Validator::make($request->all(), [
            'ticket_id' => 'required|numeric'
        ]);

        if($validator->fails()){
            return $this->error('Invalid data', 422, $validator->errors()); 
        }

        $ticket = Ticket::find($request->get('ticket_id'));

        if(!$ticket){
            return $this->error('Ticket not found', 404, [], $request->all());
        }

        if($ticket->user_id != $request->user()->id){
            return $this->error('Access unathorized', 403, [], $request->all());
        }

        try{
            DB::beginTransaction();
            $payment = Payment::create([
                'ticket_id' => $ticket->id,
                'amount' => $ticket->amount,
                'status' => STATUS_NOT_PAID,
                'description' => 'Ref.: Ticket ' . $ticket->id,
            ]);
            $result = $this->createPix($ticket, $request->user(), $payment);

            if(!$result['success']){
                DB::rollBack();
                return $this->error($result['error'], 500, [], $request->all());
            }

            $payment->update([
                'hash' => $result['data']['hash'],
                'paid_at' => $result['data']['paid_at'] ?: null,
                'expiration_at' => $result['data']['expiration_at'] ?: null,
                'payment' => $result['data']['payment'],
                'status' => $result['data']['status'],
                'reference_id' => $result['data']['reference']
            ]);
            DB::commit();
        }catch(Exception $ex){
            return $this->error('Fails in db store', 500, ['exception' => $ex->getMessage()]); 
        }

        return $this->success('Payment created with success', 200, ['payment' => $payment]);
    }
}
