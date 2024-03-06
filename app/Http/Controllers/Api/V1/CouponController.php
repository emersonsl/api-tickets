<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Traits\HttpResponses;
use Brick\Math\BigInteger;
use Exception;
use Illuminate\Support\Facades\Validator;
use Ramsey\Uuid\Type\Integer;

class CouponController extends Controller
{
    use HttpResponses;

    public function create(Request $request){
        $validator = Validator::make($request->all(), [
            'event_id' => 'required|numeric|integer|min:1',
            'key' => 'required',
            'quantity' => 'required|numeric|integer|min:1',
            'value' => 'required|numeric|integer|min:1',
            'release_date_time' => 'required|date|after:' . date('Y-m-d H:m:s'),
            'expiration_date_time' => 'required|date|after:release_date_time'
        ]);

        if($validator->fails()){
            return $this->error('Invalid data', 422, $validator->errors(), $request->all());
        }

        $event = Event::find($request->get('event_id'));

        if(!$event){
            return $this->error('Event not found', 404, [], $request->all());
        }

        try{
            $coupon = Coupon::create($validator->validated());
        }catch(Exception $ex){
            return $this->error('Fails in db store', 500, ['exception' => $ex->getMessage()], $request->all());
        }
        
        return $this->success('Coupon created with success', 200, ['sector' => $coupon]);
    }

    public static function check(string $key, int $event_id, float $value_batch = null): array{
        $coupon = Coupon::where('key', $key)->where('event_id', $event_id)->first();

        if(!$coupon){
            return ['success' => false, 'error' => 'Coupon not found'];
        }

        if(isset($value_batch) && $coupon->value > $value_batch){
            return ['success' => false, 'error' => 'Coupon cannot be applied, exceeds the maximum allowable value'];
        }

        if(strtotime($coupon->release_date_time) > strtotime(date('Y-m-d H:s'))){
            return ['success' => false, 'error' => 'Coupon cannot be applied, not realesed'];
        }

        if(strtotime($coupon->expiration_date_time) < strtotime(date('Y-m-d H:s'))){
            return ['success' => false, 'error' => 'Coupon cannot be applied, expired'];
        }

        if($coupon->quantity < 1){
            return ['success' => false, 'error' => 'Coupon cannot be applied, sold out'];
        }
        
        return ['success' => true, 'coupon' => $coupon];
    }
}
