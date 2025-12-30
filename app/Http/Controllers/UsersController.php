<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth ;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

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
            "name_ar" => "sometimes|string",
            "name_en" => "sometimes|string",
            "job_title_ar" => "sometimes|string",
            "job_title_en" => "sometimes|string",
            "device_token" => "sometimes|string",
            "old_password" => "sometimes|string",
            "password" => "sometimes|string",
            "image" => "sometimes|image|mimes:png,jpg|max:3072"
        ]);
        $user = $request->user();
        if($request->password) {
            if(!Hash::check($request->old_password , $user->password))
                return response()->json(['status'=>"failed",'code'=>"incorrect_credentials"],400);
            $data['password'] = Hash::make($request->password);
            unset($data['old_password']);
        }
       
        if($request->hasFile('image')) {
            if($user->image_path){
                $exploded_path = explode('storage/',$user->image_path);
                if(Storage::disk('public')->exists(end($exploded_path))){
                   Storage::disk('public')->delete(end($exploded_path));
                }
            }
            $store_path = "images/" . $user->id;
            $file_path = $request->file('image')->store($store_path , 'public');
            $full_path = env('APP_URL') . '/storage' . '/' . $file_path;
            $data['image_path'] = $full_path;
            unset($data['image']);
        }

        $user->update($data);
        return Response()->json(['status'=>"success","data"=>$user]);
    }
}
