<?php

namespace App\Http\Controllers\API;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\API\DocumentController;
use App\Models\Application;
use App\Models\User;
use App\Models\Document;
use App\Http\Services\ApplicationService;
use App\Http\Services\DocumentService;

use App\Http\Requests\Application\StoreAppRequest;
use App\Http\Requests\Application\EditAppRequest;


use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ApplicationController extends Controller
{
    public function __construct(ApplicationService $applicationService,DocumentService $documentationService)
    {
        $this->applicationService = $applicationService;
        $this->documentationService = $documentationService;
    }

    // get all applications
    public function index(Request $request){
        $applications = Application::all();
        return $applications;
    }

    // get application by id
    public function show($id){
        $application = Application::with('student')->where('id', $id)->firstOrFail();
        return response()->json($application, 200);
    }

    // store application
    public function store(StoreAppRequest $request){
        try{
            //dd($request->all());
            $validated = $request->validated();
            $student_id= $request->user()->id;
            $application = $this->applicationService->store($validated,$student_id);
            $application_id = $application['id'];
            $Docs = $this->documentationService->storeDocs($validated,$application_id);
        }catch(Exception $ex){
            return response()->json($application, 400);
        }
        return response()->json($application, 201);
    }

    // edit application's docs
    public function edit(EditAppRequest $request, $app_id)
    {
        $validated = $request->validated();

        foreach ($validated as $key => $file) {
            $oldDocument = Document::where('application_id', $app_id)->where('document_type', $key)->first();  
            if ($oldDocument) {
                $this->documentationService->deleteDoc($oldDocument->id);
            }
            $this->documentationService->store($file, $key, $app_id);
        }

        return response()->json(['message' => 'Documents updated successfully.'], 200);
    }

    // update application's stage to the next stage
    public function toNextStage($id)
    {
        $message = $this->applicationService->toNextStage($id);
        return response()->json([
            'message' => $message,
        ]);
    }

    // reject application
    public function rejectApp($id)
    {
        $application = $this->applicationService->reject($id);
        return response()->json($application, 200);
    }

    // get all application for specific student
    public function getAppsByStudentId($id){
        $applications = $this->applicationService->getAppsByStudentId($id);
        return response()->json($applications, 200);
    }

    // get all applications for the logged in student
    public function getAppsByUserId(Request $request){
        $student_id= $request->user()->id;
        $applications = $this->applicationService->getAppsByStudentId($student_id);
        return response()->json($applications, 200);
    }

    // get all pending_admin apps
    public function get_pending_admin_Apps(){
        $applications = $this->applicationService->getApplicationByStage('pending_admin');
        return response()->json($applications, 200);
    }

    // get all under_review apps
    public function get_under_review_Apps(){
        $applications = $this->applicationService->getApplicationByStage('under_review');
        return response()->json($applications, 200);
    }

    // get all approved_by_reviewer apps
    public function get_final_review_Apps(){
        $applications = $this->applicationService->getApplicationByStage('final_review');
        return response()->json($applications, 200);
    }

    // get all awaiting_payment apps
    public function get_awaiting_payment_Apps(){
        $applications = $this->applicationService->getApplicationByStage('awaiting_payment');
        return response()->json($applications, 200);
    }

    // get all approved apps
    public function get_approved_Apps(){
        $applications = $this->applicationService->getApplicationByStage('approved');
        return response()->json($applications, 200);
    }

    // get all rejected apps
    public function get_rejected_Apps(){
        $applications = $this->applicationService->getApplicationByStage('rejected');
        return response()->json($applications, 200);
    }


}
