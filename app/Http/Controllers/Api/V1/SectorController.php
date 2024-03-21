<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Sector;
use App\Traits\HttpResponses;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;

class SectorController extends Controller
{
    use HttpResponses;

    public function index(){
        $sectors = Sector::all();

        return $this->success('List of Sectors', 200, ['sectors' => $sectors]);
    }

    private function makeValidator(Request $request, bool $create = true){
        $fields = [
            'title' => 'required'
        ];

        if($create){
            $fields['event_id'] = 'required|numeric|integer|min:1';
        }

        return Validator::make($request->all(), $fields);
    }

    public function create(Request $request){
        $validator = $this->makeValidator($request);

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

    public function update(Request $request, Sector $sector){
        $validator = $this->makeValidator($request, false);
        
        if($validator->fails()){
            return $this->error('Invalid data', 422, $validator->errors(), $request->all());
        }

        try{
            $sector->update($validator->validated());
        }catch(Exception $ex){
            return $this->error('Fails in db store', 500, ['exception' => $ex->getMessage()], $request->all());
        }

        return $this->success('Sector updated with success', 200, ['sector' => $sector]);
    }

    public function destroy(Sector $sector){
        try{
            $sector->forceDelete();
            return $this->success('Sector deleted with success', 200, ['event' => $sector]);
        }catch(QueryException $ex){
            $sector = Event::find($sector->id);
            $sector->delete();
            return $this->success('Sector canceled with success, there are associated tickets', 200, ['sector' => $sector]);
        }catch(Exception $ex){
            return $this->error('Fails in db remove', 500, ['exception' => $ex->getMessage()], [$sector]);
        }
    }

}
