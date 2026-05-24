<?php

namespace App\Http\Services;

use App\Models\Application;
use App\Models\Document;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ApplicationService
{
    public function store($validated,$student_id)
    {
        // application attributes
        $validatedApp = [
            'title' => $validated['title'],
            'principal_investigator' => $validated['principal_investigator'],
            'co_investigators' => $validated['co_investigators'],
            'keywords' => $validated['keywords'],
        ];
        $validatedApp['student_id']=$student_id;
        $validatedApp['current_stage']='pending_admin';
        $application = Application::create($validatedApp);

        // update application to add serial_number
        $year = date("Y");
        $application->serial_number = "IRB-$year-" . str_pad($application->id, 5, "0", STR_PAD_LEFT);
        $application->save();

        return $application;
    }

    public function getAppsByStudentId($id)
    {
        $applications = Application::where("student_id",$id)->get();
        return $applications;
    }

    public function getApplicationByStage($stage)
    {
        $applications = Application::where('current_stage',$stage)->get();
        return $applications;
    }

    

}