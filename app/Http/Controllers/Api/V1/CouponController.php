<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Traits\HttpResponses;
use Exception;
use Illuminate\Support\Facades\Validator;

class CouponController extends Controller
{
    use HttpResponses;

    public function create(Request $request){
        $validator = Validator::make($request->all(), [
            'event_id' => 'required|numeric',
            'key' => 'required',
            'quantity' => 'required|numeric',
            'value' => 'required|numeric',
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
        
        return $this->success('Sector created with success', 200, ['sector' => $coupon]);
    }
}
