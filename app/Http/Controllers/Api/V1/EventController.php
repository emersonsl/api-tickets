<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Http\Resources\EventResourceWithBatchWithSector;
use App\Mail\EventCreatedMail;
use App\Models\Address;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;
use App\Traits\HttpResponses;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class EventController extends Controller
{
    use HttpResponses;

    public function index(){
        $data = EventResource::collection(Event::all());
        
        return $this->success('List of Events', 200, ['events' => $data]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'address' => 'required',
            'event' => 'required'
        ]);

        if($validator->fails()){
            return $this->error('Invalid data', 422, $validator->errors());
        }

        $validatorAddress = Validator::make($request->get('address'), [
            'street' => 'required',
            'district' => 'required',
            'number' => 'required|numeric|integer|min:1',
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
        
        $responseArray = ['event' => $event];

        try{
            $this->sendMailToAdmin($event, $user);
        }catch (Exception $ex){
            $responseArray['message'] = $ex->getMessage();
        }

        return $this->success('Event created with success', 200, $responseArray);
    }

    private function sendMailToAdmin(Event $event, User $promoter): void {
        $admin = UserController::getFirstAdmin();

        Mail::to($admin)->queue(new EventCreatedMail($event, $promoter, $admin));
    }

    public function listUpcoming(){
        $data = EventResource::collection(Event::where('date_time', '>=', 'now()')->get());

        return $this->success('List of Events Upcoming', 200, ['events' => $data]);
    }

    public function listAvailable(){

        $data = Event::selectRaw('events.*, events.address_id, batches.id as batch_id, sectors.id as sector_id, row_number() over(partition by events.id, sectors.id order by batches.id)')
        ->join('batches', 'events.id', '=', 'batches.event_id')
        ->join('sectors', 'sectors.id', '=', 'batches.sector_id')
        ->where('events.date_time', '>=', 'now()')
        ->where('batches.expiration_date_time', '>=', 'now()')
        ->where('batches.release_date_time', '<=', 'now()')
        ->where('batches.quantity', '>', '0')
        ->get();

        $this->filterData($data);

        $data = EventResourceWithBatchWithSector::collection($data);

        return $this->success('List of Events Available', 200, ['events' => $data]);
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

    public function uploadBanner(Request $request){
        $validator = Validator::make($request->all(), [
            'event_id' => 'required|numeric|integer|min:1',
            'banner' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if($validator->fails()){
            return $this->error('Invalid data', 422, $validator->errors(), $request->all());
        }

        $event = Event::find($request->get('event_id'));

        if(!$event){
            return $this->error('Event not found', 404, [], $request->all());
        }

        $folder_path = "event/$event->id/banner";
        $path = $request->file('banner')->store($folder_path);

        $event->banner_url = $path;
        $event->save();
        
        return $this->success('Banner upload with success', 200, ['event' => $event]);
    }
}
