<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth ;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersController extends Controller
{
    public function createUser(Request $request) {
        $data = $request->validate([
            "name_ar" => "required|string",
            "name_en" => "required|string",
            "job_title_ar" => "required|string",
            "job_title_en" => "required|string",
            "email" => "required|email",
            "password" => "required|string",
        ]);
        $user = User::create([
            "name_ar" => $data['name_ar'],
            "name_en" => $data['name_en'],
            "job_title_ar" => $data['job_title_ar'],
            "job_title_en" => $data['job_title_en'],
            "email" => $data['email'],
            "password" => Hash::make($data['password']),
        ]);
        $user->access_token = $user->createToken('access_token')->plainTextToken;
        return response()->json(['status'=>"success","data"=>$user]);
       
    }

    public function login(Request $request){
        $request->validate([
            'email' => "required|email",
            "password" => "required|string",
        ]);
        $updated_data = $request->validate([
            "device_token" => "sometimes|string|nullable",
            "lang" => "sometimes|string",
        ]);
        if(!Auth::attempt($request->only('email','password')))
            return response()->json(['status'=>"failed",'code'=>"incorrect_credentials"],400);
        
        $user = User::where('email' , $request->email)->first();
        $user->update($updated_data);
        $user->access_token = $user->createToken('access_token')->plainTextToken;
        return response()->json(['status'=>"success" , "data"=>$user]);
    }

    public function updateUserData(Request $request){
        $data = $request->validate([
            "lang" => "sometimes|string",
        ]);
        return $data;
    }
}
