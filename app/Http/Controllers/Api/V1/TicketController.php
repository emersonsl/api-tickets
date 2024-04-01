<?php

namespace App\Http\Controllers\Api\V1;

use App\Constants;
use App\Http\Controllers\Controller;
use App\Http\Resources\TicketResource;
use App\Jobs\CancelPayment;
use App\Models\Batch;
use App\Models\Coupon;
use App\Models\Payment;
use App\Models\Ticket;
use App\Models\User;
use App\Traits\HttpResponses;
use Brick\Math\BigInteger;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    use HttpResponses;

    public function index(){
        $data = TicketResource::collection(Ticket::all());
        
        return $this->success('List of Batches', 200, ['batches' => $data]);
    }

    public function create(Request $request){
        $validator = Validator::make($request->all(), [
            'batch_id' => 'required|numeric|integer|min:1',
            'key_coupon' => 'nullable',
        ]);

        if($validator->fails()){
            return $this->error('Invalid data', 422, $validator->errors()); 
        }

        $batch = Batch::find($request->get('batch_id'));

        if(!$batch){
            return $this->error('Batch not found', 404, [], $request->all());
        }

        $key_coupon = $request->get('key_coupon');
        if(isset($key_coupon)){
            $checkCoupon = CouponController::check($key_coupon, $batch->event_id, $batch->value);
            if(!$checkCoupon['success']){
                return $this->error($checkCoupon['error'], 404, [], $request->all());
            }
            $coupon = $checkCoupon['coupon'];
        }else{
            $coupon = null;
        }

        return $this->storeTiket($request, $batch, $coupon);
    }

    private function storeTiket(Request $request, Batch $batch, Coupon $coupon = null, ){
        try{
            DB::beginTransaction();
            if($coupon){
                Coupon::find($coupon->id)->decrement('quantity');
                if(Coupon::find($coupon->id)->quantity < 0){
                    DB::rollBack();
                    return $this->error('Coupon cannot be applied, sold out', 404, [], $request->all());
                }
            }

            Batch::find($batch->id)->decrement('quantity');
            if(Batch::find($batch->id)->quantity < 0){
                DB::rollBack();
                return $this->error('Ticket cannot be reserved, sold out', 404, [], $request->all());
            }
            $ticketData = $this->getTicketData($batch, $coupon, $request->user());
            $ticket = Ticket::create($ticketData);
            DB::commit();
        }catch(Exception $ex){
            DB::rollBack();
            return $this->error('Error in stored db', 500, ['exception' => $ex->getMessage()], [$request->all()]); 
        }

        return $this->success('Ticket reserved with success', 200, ['ticket' => $ticket]);
    }

    private function getTicketData(Batch $batch, Coupon $coupon = null, User $user){
        $discount = $coupon ? $coupon->value : 0;
        $ticketData = [
            'user_id' => $user->id,
            'batch_id' => $batch->id,
            'coupon_id' => $coupon ? $coupon->id : null,
            'value' => $batch->value,
            'value_discount' => $discount,
            'amount' => $batch->value - $discount
        ];
        return $ticketData;
    }

    public function destroy(Request $request, Ticket $ticket){
        $user = $request->user();
        if(!$user->hasRole('admin') && $ticket->user_id != $user->id){
            return $this->error('This action is unauthorized. Only the owner or admin can cancel a ticket', 403, []);
        }

        //cancelar o pagamento, devolver valor
        $payment = Payment::where('ticket_id', $ticket->id)->where('status', Constants::PAYMENT_STATUS_PAID)->orderBy('created_at', 'desc')->get()->first();

        if($payment){
            CancelPayment::dispatch($payment);
        }else{
            $result = $this->cancelTicket($ticket);

            if($result['success']){
                //enviar e-mail para dono informando o cancelamento
                return $this->success('Ticket canceled with success', 200, ['event' => new TicketResource($ticket)]);
            }else{
                return $this->error('Fails in db remove', 500, ['exception' => $result['message']], [$ticket]);
            }
        }
    }

    private function cancelTicket(Ticket $ticket){
        try{
            DB::beginTransaction();
            
            if($ticket->coupon_id){
                Coupon::find($ticket->coupon_id)->increment('quantity');
            }
            
            Batch::find($ticket->batch_id)->increment('quantity');

            $ticket->delete();
            
            DB::commit();
            return ['success' => true, 'data' => $ticket];
        }catch(Exception $ex){
            DB::rollBack();
            return ['success' => false, 'message' => $ex->getMessage()]; 
        }
    }
}
