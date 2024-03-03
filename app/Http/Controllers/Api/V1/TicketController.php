<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Coupon;
use App\Models\Ticket;
use App\Models\User;
use App\Traits\HttpResponses;
use Brick\Math\BigInteger;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;

class TicketController extends Controller
{
    use HttpResponses;

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
}
