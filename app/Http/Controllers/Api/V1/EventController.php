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
use Illuminate\Database\QueryException;
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

    private function makeValidator(Request &$request, bool $create = true){
        return Validator::make($request->all(), [
            'address' => $create ? 'required' : 'nullable',
            'event' => 'required'
        ]);
    }

    private function makeValidatorAddress(Request &$request, bool $create = true){
        return Validator::make($request->get('address'), [
            'street' => $create ? 'required' : 'nullable',
            'district' => $create ? 'required' : 'nullable',
            'number' => ($create ? 'required' : '') . '|numeric|integer|min:1',
            'city' => $create ? 'required' : 'nullable',
            'state' => $create ? 'required' : 'nullable',
            'country' => $create ? 'required' : 'nullable',
            'post_code' => $create ? 'required' : 'nullable',
            'complement' => 'nullable',        
        ]);
    }

    private function makeValidatorEvent(Request &$request, bool $create = true){
        $fields = [
            'title' => $create ? 'required' : 'nullable',
            'date_time' => $create ? 'required|date|after:' . date('Y-m-d H:m:s') : 'nullable'
        ];

        if(!$create){
            $fields['id'] = 'required';
        }

        return Validator::make($request->get('event'), $fields);
    }

    private function storeEvent(Array $eventData, Array $addressData, User $user){
        try{
            DB::beginTransaction();
            $address = Address::create($addressData);
            
            $eventData['address_id'] = $address->id;
            $eventData['create_by'] = $user->id;

            $event = Event::create($eventData);
            DB::commit();
        }catch(Exception $ex){
            DB::rollBack();
            return ['success' => false, 'message' => $ex->getMessage()];
        }

        return ['success' => true, 'data' => $event];
    }
    
    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $validator = $this->makeValidator($request);

        if($validator->fails()){
            return $this->error('Invalid data', 422, $validator->errors());
        }

        $validatorAddress = $this->makeValidatorAddress($request);
        $validatorEvent = $this->makeValidatorEvent($request);

        if($validatorAddress->fails() || $validatorEvent->fails()){
            return $this->error('Invalid data', 422, [
                'address' => $validatorAddress->errors(),
                'event' => $validatorEvent->errors()
            ]); 
        }

        $user = $request->user();

        $resultStoreEvent = $this-> storeEvent(
            $validatorEvent->validated(),
            $validatorAddress->validated(),
            $user
        );

        if(!$resultStoreEvent['success']){
            return $this->error('Error in stored db', 500, ['exception' => $resultStoreEvent['message']], [$request->all()]); 
        }
        
        $event = $resultStoreEvent['data'];
        $responseArray = ['event' => new EventResource($event)];

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

        $data = Event::selectRaw('events.*, batches.id as batch_id, sectors.id as sector_id, row_number() over(partition by events.id, sectors.id order by batches.id)')
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

    public function update(Request $request){
        $validator = $this->makeValidator($request, false);
        
        if($validator->fails()){
            return $this->error('Invalid data', 422, $validator->errors(), $request->all());
        }

        $errors = [];
        if($request->get('address')){
            $validatorAddress = $this->makeValidatorAddress($request, false);
            if($validatorAddress->fails()){
                $errors['address'] = $validatorAddress->errors();
            }
        }
        
        $validatorEvent = $this->makeValidatorEvent($request, false);
        
        if($validatorEvent->fails()){
            $errors['event'] = $validatorEvent->errors();
        }
        
        if(!empty($errors)){
            return $this->error('Invalid data', 422, $errors); 
        }
        
        $event = Event::find($request->get('event')['id']);

        if(!$event){
            return $this->error('Event not found', 404, [], $request->all());
        }

        try{
            if($request->get('address')){
                Address::find($event->address_id)->update($validatorAddress->validated());
            }
            $event->update($validatorEvent->validated());
        }catch (Exception $ex){
            return $this->error('Fails in db store', 500, ['exception' => $ex->getMessage()], $request->all());
        }

        return $this->success('Event updated with success', 200, ['event' => new EventResource($event)]);
    }

    public function destroy(Event $event){
        try{
            $event->forceDelete();
            return $this->success('Event deleted with success', 200, ['event' => new EventResource($event)]);
        }catch(QueryException $ex){
            $event = Event::find($event->id);
            $event->delete();
            return $this->success('Event canceled with success, there are associated tickets', 200, ['event' => new EventResource($event)]);
        }catch(Exception $ex){
            return $this->error('Fails in db remove', 500, ['exception' => $ex->getMessage()], [$event]);
        }
    }   
}
