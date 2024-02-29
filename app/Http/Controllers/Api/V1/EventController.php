<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Event;
use Illuminate\Http\Request;
use App\Traits\HttpResponses;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class EventController extends Controller
{
    use HttpResponses;
    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $validatorAddress = Validator::make($request->get('address'), [
            'street' => 'required',
            'district' => 'required',
            'number' => 'required|numeric',
            'city' => 'required',
            'state' => 'required',
            'country' => 'required',
            'post_code' => 'required',
            'complement' => 'nullable'
        ]);

        $validatorEvent = Validator::make($request->get('event'), [
            'title' => 'required',
            'date_time' => 'required|date|after:' . date('Y-m-d H:m:s')
        ]);

        if($validatorAddress->fails() || $validatorEvent->fails()){
            return $this->error('Invalid data', 422, [
                'address' => $validatorAddress->errors(),
                'event' => $validatorEvent->errors()
            ]); 
        }
        try{
            DB::beginTransaction();
            $address = Address::create($validatorAddress->validated());
            $user = $request->user();
            
            $eventData = [
                'address_id' => $address->id,
                'create_by' => $user->id,
            ];
            $eventData = array_merge($eventData, $validatorEvent->validated());
            $event = Event::create($eventData);
            DB::commit();
        }catch(Exception $ex){
            DB::rollBack();
            return $this->error('Error in stored db', 500, ['exception' => $ex->getMessage()], [$request->all()]); 
        }

        return $this->success('Event created with success', 200, ['event' => $event]);
    }
}
