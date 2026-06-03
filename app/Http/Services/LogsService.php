<?php

namespace App\Http\Services;

use App\Models\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class LogsService {
    public function index(){
        return Log::latest()->paginate(15);
    }

    public function store($application_id, $user_id, $action, $type){
        if(!in_array($type, ['submission','assignment','status_change','certificate','decision','auth','modify_application','other'])){
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
        return Log::where('application_id', $app_id)->get();
    }

    public function getLogsByUserId($user_id){
        return Log::where('user_id', $user_id)->latest()->paginate(15);
    }

    public function getLogsByType($type){
        return Log::where('type', $type)->latest()->paginate(15);
    }

}