<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\log;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB ;
use PgSql\Lob;

class DevicesController extends Controller
{

    public function createDevice(Request $request) {
        $request->validate([
            "base_data.serial_number" => "required|integer",
            "base_data.organization_name" =>"required|string",
            "base_data.software_release" =>"required|string",
            "base_data.first_launch" => "required|date",
            "base_data.history_length" => "required|string",
            "details" => "required|array",
            "details.*.date" => "required|date",
            "details.*.time" => "required|date_format:H:i:s",
            "details.*.percentage" => "required|numeric",
        ]);
        $device = Device::where('serial_number',$request->base_data['serial_number'])->first();
        if(!$device)
            $device = Device::create($request->base_data);

        $last_log = Log::orderBy('datetime', 'desc')->first();
      
        #region [ ===> Data Processing <=== ]
        $sorted_logs = collect($request->details)->map(function($log) use($device) {
            $log['device'] = $device->id;
            $log['datetime'] = Carbon::createFromFormat('Y-m-d H:i:s',$log['date'] . ' ' . $log['time']);
            return $log;
        })->sortBy(fn($log)=>$log['datetime']);
        
        $last_datetime = $last_log? Carbon::createFromFormat('Y-m-d H:i:s',$last_log->datetime) : null;

        $new_logs = $sorted_logs->filter(function($log)use($last_datetime){
            if(!$last_datetime) return true;
            $current_datetime = Carbon::createFromFormat('Y-m-d H:i:s', $log['datetime']);
            if($current_datetime > $last_datetime) return true;
        });
        
        $low_helium_level_logs = $new_logs->where('percentage', '<=' , 50);
       
        if($low_helium_level_logs->isNotEmpty()) {
            $device_users = DB::table('users_devices')->where('device','=',$device->id)->select('user')->get();
            if($device_users->isNotEmpty()) {
                foreach($device_users as $user){
                    $message = "Low_level.range50";
                    $range30 = $new_logs->where('percentage', '<' , 50);
                    if($range30) $message = "Low_level.range50";
                    $quench = $new_logs->where('percentage', '<' , 30);
                    if($quench) $message = "Low_level.quench";
                    $user = User::find($user->user);
                    app()->setLocale($user->lang);
                    $payload['token'] = $user->device_token;
                    $payload['title'] = $device->organization_name;
                    $payload['body'] = __("messages.".$message,[
                        "serial" => $device->serial_number,
                    ]);
                    FirebaseController::sendNotification($payload);
                }
            }
        }
        #endregion

        
        if(count($new_logs) > 0)
            DB::table('devices_logs')->insert($new_logs->values()->toArray());

        return response()->json(['status'=>"success",'code'=>"data_saved"]);
    }


    public function addUserToDevice(int $serial_number , Request $request){
        $device = Device::where('serial_number',$serial_number)->first();
        $user = $request->user();

        if(!$device)
            return response()->json(['status'=>"failed", "code"=> "not_found"],400);

        $query = DB::table('users_devices');
        $query->where('device','=',$device->id);
        $query->where('user' , '=' , $user->id);
        $result = $query->select()->get();
        
        if($result->isNotEmpty())
            return response()->json(['status'=>'failed','code'=>'already_registered']);

        DB::table('users_devices')->insert(["device" => $device->id,"user" => $user->id]);
        return response()->json(['status'=>"success","code"=>"data_saved"]); 
    }


    public function fetchDevices(Request $request) {
        try{
            $user = $request->user();
            $query = DB::table("devices as dev");
            $query->leftJoin('users_devices as per','per.device' , '=' , 'dev.id');
            $query->where('per.user','=',$user->id);
            $result = $query->select("dev.*")->get();        
            return response()->json(['status' => "success", 'data' => $result]);
        }catch(Exception $ex){
            dd($ex);
        }

    }

    public function fetchDeviceLogs(int $id , Request $request) {
      

        $request->validate([
            "start_date" => "required|date",
            "end_date" => "required|date",
        ]);

        $query = DB::table('devices_logs');
        $query->where('device','=',$id);
        $query->where('date', '>=', $request->start_date);
        $query->where('date', '<=', $request->end_date);
        $result = $query->select()->get();

        return response()->json(['status'=>"success",'data'=>$result]);
    }
}
