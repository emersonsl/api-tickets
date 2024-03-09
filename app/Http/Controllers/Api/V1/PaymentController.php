<?php

namespace App\Http\Controllers\Api\V1;

use App\Constants;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessPayment;
use App\Models\Payment;
use App\Models\Ticket;
use Illuminate\Support\Facades\Validator;
use App\Traits\HttpResponses;
use App\Traits\Paggue;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    use HttpResponses;

    public function create(Request $request){
        $validator = Validator::make($request->all(), [
            'ticket_id' => 'required|numeric|integer|min:1'
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
                'status' => Constants::PAYMENT_STATUS_NOT_PROCESS,
                'description' => 'Ref.: Ticket ' . $ticket->id,
            ]);
            
            ProcessPayment::dispatch($payment, $request->user(), $ticket);

            DB::commit();
        }catch(Exception $ex){
            DB::rollBack();
            return $this->error('Fails in db store', 500, ['exception' => $ex->getMessage()]); 
        }

        return $this->success('Payment created with success', 200, ['payment' => $payment]);
    }
}
