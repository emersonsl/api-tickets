<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Batch;
use App\Models\Event;
use Illuminate\Http\Request;
use App\Traits\HttpResponses;
use Exception;
use Illuminate\Database\Query\JoinClause;
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

    public function listUpcoming(){
        $data = Event::join('addresses', 'addresses.id', '=', 'events.address_id')
                ->where('date_time', '>=', 'now()')
                ->get();
        
        if(!$data){
            $this->error('Events Upcoming not found', 404, []);
        }

        return $this->success('List of Events Upcoming', 200, ['events' => $data]);
    }

    public function listAvailable(){

        $data = Event::selectRaw('*, row_number() over(partition by events.id order by batches.id)')
        ->join('addresses', 'addresses.id', '=', 'events.address_id')
        ->join('batches', 'events.id', '=', 'batches.event_id')
        ->where('events.date_time', '>=', 'now()')
        ->where('batches.expiration_date_time', '>=', 'now()')
        ->where('batches.release_date_time', '<=', 'now()')
        ->where('batches.quantity', '>', '0')
        ->get();

        if(!$data){
            $this->error('Events available not found', 404, []);
        }

        $this->filterData($data);

        return $this->success('List of available Upcoming', 200, ['events' => $data]);
    }

    private function filterData(&$data){
        $newData = [];
        foreach($data as $element){
            if($element->row_number == 1){
                unset($element->row_number);
                $newData[] = $element;
            }
        }
        $data = $newData;
    }
}
