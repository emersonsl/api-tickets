<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\HttpResponses;
use Exception;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    use HttpResponses;

    public function create(Request $request){
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|numeric|integer|min:5',
            'cpf_cnpj' => 'required|numeric|integer|min_digits:9|max_digits:14',
            'name' => 'required',
            'email' => 'required',
            'password' => 'required'
        ]);

        if($validator->fails()){
            return $this->error('Invalid data', 422, $validator->errors()); 
        }

        try{
            $user = User::where('email', $request->get('email'))->first();
            if($user){
                return $this->error('Email already exists in the database', 409, [], $request->all()); 
            }
            $user = User::create($validator->validated());
            $user->assignRole('customer');
            
            return $this->success('Success in register user', 200, $validator->validated());
        }catch(Exception $ex){
            return $this->error('Fails in db store', 500, ['exception' => $ex->getMessage()]); 
        }   
    }

    public function promote(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'role' => 'required',
        ]);

        if($validator->fails()){
            return $this->error('Invalid data', 422, $validator->errors()); 
        }
        
        $user = User::where('email', $request->get('email'))->first();
        if(!$user){
            $errors[] = 'User not found'; 
        }

        $role = Role::where('name', $request->get('role'))->first();
        if(!$role){
            $errors[] = 'Role not found';
        }

        if(isset($errors)){
            return $this->error('Itens not found', 404, ['itens' => $errors], $request->all()); 
        }
        
        $user->assignRole($role);
        
        return $this->success('Success in promote user', 200, ['user' => $user, 'role' => $role, 'user' => $user]);
    }

    public static function getFirstAdmin(): User{
        $admin = User::join('model_has_roles', 'users.id', '=','model_has_roles.model_id')
                ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->where('roles.name', '=','admin')->first();
        return $admin;
    }
}
