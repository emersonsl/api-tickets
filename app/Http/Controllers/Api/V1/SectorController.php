<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Sector;
use App\Traits\HttpResponses;
use Exception;
use Illuminate\Support\Facades\Validator;

class SectorController extends Controller
{
    use HttpResponses;

    public function create(Request $request){
        $validator = Validator::make($request->all(), [
            'event_id' => 'required|numeric|integer|min:1',
            'title' => 'required'
        ]);

        if($validator->fails()){
            return $this->error('Invalid data', 422, $validator->errors(), $request->all());
        }

        $event = Event::find($request->get('event_id'));

        if(!$event){
            return $this->error('Event not found', 404, [], $request->all());
        }

        try{
            $sector = Sector::create($validator->validated());
        }catch(Exception $ex){
            return $this->error('Fails in db store', 500, ['exception' => $ex->getMessage()], $request->all());
        }
        
        return $this->success('Sector created with success', 200, ['sector' => $sector]);
    }
}
