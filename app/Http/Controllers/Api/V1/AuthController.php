<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    use HttpResponses;

    public function login(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required'
        ]);

        if($validator->fails()){
            return $this->error('Invalid data', 422, $validator->errors()); 
        }

        if(Auth::attempt($request->only('email', 'password'))){

            return $this->success('Authorized', 200, [
                'token' => $request->user()->createToken('user')
            ]);
        }
        return $this->error('Unauthorized', 403);
    }

    public function logout(Request $request){
        $request->user()->currentAccessToken()->delete();

        return $this->success('Logout success', 200);
    }
}
