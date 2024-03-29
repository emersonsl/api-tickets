<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\BatchResource;
use App\Models\Batch;
use App\Models\Event;
use App\Models\Sector;
use App\Traits\HttpResponses;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;

class BatchController extends Controller
{
    use HttpResponses;

    public function index(){
        $data = BatchResource::collection(Batch::all());
        
        return $this->success('List of Batches', 200, ['batches' => $data]);
    }

    public function create(Request $request){
        $validator = Validator::make($request->all(), [
            'event_id' => 'required|numeric|integer|min:1',
            'sector_id' => 'required|numeric|integer|min:1',
            'title' => 'required',
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
        
        $sector = Sector::find($request->get('sector_id'));
        
        if(!$sector){
            return $this->error('Sector not found', 404, [], $request->all());
        }
        
        try{
            $batch = Batch::create($validator->validated());
        }catch(Exception $ex){
            return $this->error('Error in stored db', 500, ['exception' => $ex->getMessage()], $request->all());
        }

        return $this->success('Batch created with sucesss', 200, ['batch' => new BatchResource($batch)]);
    }

    public function destroy(Batch $batch){
        try{
            $batch->forceDelete();
            return $this->success('Batch deleted with success', 200, ['batch' => new BatchResource($batch)]);
        }catch(QueryException $ex){
            $batch = Batch::find($batch->id);
            $batch->delete();
            return $this->success('Batch canceled with success, there are associated tickets', 200, ['batch' => new BatchResource($batch)]);
        }catch(Exception $ex){
            return $this->error('Fails in db remove', 500, ['exception' => $ex->getMessage()], [$batch]);
        }
    }  
}
