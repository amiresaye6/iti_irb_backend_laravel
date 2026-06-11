<?php

namespace App\Http\Services;

use App\Models\Application;
use App\Models\Document;
use App\Models\Review;
use App\Models\ReviewComment;
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
                $message = "تم تحديث مرحلة الطلب من المراجعة الأولية إلى قيد المراجعة";
                break;

            case 'under_review':
                $application->current_stage = 'final_review';
                $message = "تم تحديث مرحلة الطلب من قيد المراجعة إلى المراجعة النهائية";
                break;

            case 'final_review':
                $application->current_stage = 'awaiting_payment';
                $message = "تم تحديث مرحلة الطلب من المراجعة النهائية إلى في انتظار الدفع";
                break;

            case 'awaiting_payment':
                $application->current_stage = 'approved';
                $message = "تم تحديث مرحلة الطلب من في انتظار الدفع إلى مقبول";
                break;

            case 'approved':
            case 'rejected':
                $message = "الطلب في مرحلة نهائية ولا يمكن تحديثه";
                break;
                
            default:
                $message = "مرحلة حالية غير صالحة";
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

    public function toggle_needsModification($id){
        $application = Application::findOrFail($id);
        $application->needs_modification = !$application->needs_modification;
        $application->save();
        return $application;
    }

    
    public function getCommentsByApplicationId($id){
        //with user too
        $comments = Review::where('application_id', $id)->with('reviewer')->with('comments')->get();
        return $comments;
    }
}