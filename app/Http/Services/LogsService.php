<?php

namespace App\Http\Services;

use App\Models\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class LogsService {
    public function index(){
        return Log::with(['application', 'user'])->latest()->paginate(20);
        //return Log::latest()->paginate(15);
    }

    public function store($application_id, $user_id, $action, $type){
        if(!in_array($type, ['submission','assignment','status_change','certificate','decision','auth','modify_application','payment','other'])){
            $type = 'other';
        }
        return Log::create([
            'application_id' => $application_id,
            'user_id' => $user_id??auth()->id(),
            'action' => $action??'معلومة غير متوفرة',
            'type' => $type??'other',
        ]);
    }

    public function getLogsByAppId($app_id){
        //with application and user
        return Log::with(['application', 'user'])->where('application_id', $app_id)->get();
    }

    public function getLogsByUserId($user_id){
        return Log::with(['application', 'user'])->where('user_id', $user_id)->latest()->paginate(20);
    }

    public function getLogsByType($type){
        return Log::with(['application', 'user'])->where('type', $type)->latest()->paginate(20);
    }

    public function getLogsBySerialNumber($serial_number){
        return Log::with(['application', 'user'])->whereHas('application', function($query) use ($serial_number){
            $query->where('serial_number', $serial_number);
        })->latest()->paginate(20);
    }

}