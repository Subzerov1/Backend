<?php

use App\Http\Controllers\DevicesController;
use App\Http\Controllers\UsersController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('users/create',[UsersController::class,"createUser"]);
Route::get('users/login',[UsersController::class,"login"]);
Route::post('devices/create',[DevicesController::class,"createDevice"]);

Route::middleware("auth:sanctum")->group(function(){
    Route::post('users/update',[UsersController::class,"updateUserData"]);

    Route::get('devices/fetch',[DevicesController::class,"fetchDevices"]);
    Route::get('devices/{id}/fetch/logs',[DevicesController::class,"fetchDeviceLogs"]);
    Route::delete('devices/{id}/users/remove',[DevicesController::class,"removeUserFromDevice"]);
    Route::post('devices/{serial_number}/add_to_user',[DevicesController::class,"addUserToDevice"]);
});