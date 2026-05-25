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

    public function toNextStage($id)
    {
        $application = Application::findOrFail($id);
        $message ="";
        switch ($application->current_stage) {

            case 'pending_admin':
                $application->current_stage = 'under_review';
                $message = "stage updated from pending_admin to under_review";
                break;

            case 'under_review':
                $application->current_stage = 'approved_by_reviewer';
                $message = "stage updated from under_review to approved_by_reviewer";
                break;

            case 'approved_by_reviewer':
                $application->current_stage = 'awaiting_payment';
                $message = "stage updated from approved_by_reviewer to awaiting_payment";
                break;

            case 'awaiting_payment':
                $application->current_stage = 'approved';
                $message = "stage updated from awaiting_payment to approved";
                break;

            case 'approved':
            case 'rejected':
                $message = "Application is already in final stage";
                break;
                
            default:
                $message = "Invalid current stage";
        }

        $application->save();

        return $message;
    }

    public function reject($id)
    {
        $application = Application::findOrFail($id);
        $application->current_stage = 'rejected';
        $application->save();
        return $application;
    }

}