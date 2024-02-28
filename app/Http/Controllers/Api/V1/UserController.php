<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\HttpResponses;
use Exception;

;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    use HttpResponses;

    public function index(){
        return User::find(1);
    }

    public function create(Request $request){
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|numeric',
            'cpf_cnpj' => 'required|numeric',
            'name' => 'required',
            'email' => 'required',
            'password' => 'required'
        ]);

        if($validator->fails()){
            return $this->error('Invalid data', 422, $validator->errors()); 
        }

        try{
            User::create($validator->validated());
            return $this->success('Success in register user', 200, $validator->validated());
        }catch(Exception $e){
            return $this->error('Fails in db store', 500, ['exception' => $e->getMessage()]); 
        }
        
    }
}
