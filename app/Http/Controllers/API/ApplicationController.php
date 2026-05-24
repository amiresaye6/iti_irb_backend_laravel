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


use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ApplicationController extends Controller
{
    public function __construct(ApplicationService $applicationService,DocumentService $documentationService)
    {
        $this->applicationService = $applicationService;
        $this->documentationService = $documentationService;
    }

    public function index(Request $request){
        $applications = Application::all();
        return $applications;
    }

    public function show($id){
        $application = Application::with('student')->where('id', $id)->firstOrFail();
        return response()->json($application, 200);
    }

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

    public function getAppsByStudentId($id){
        $applications = $this->applicationService->getAppsByStudentId($id);
        return response()->json($applications, 200);
    }

    public function getAppsByUserId(Request $request){
        $student_id= $request->user()->id;
        $applications = $this->applicationService->getAppsByStudentId($student_id);
        return response()->json($applications, 200);
    }

    public function get_pending_admin_Apps(){
        $applications = $this->applicationService->getApplicationByStage('pending_admin');
        return response()->json($applications, 200);
    }

    public function get_under_review_Apps(){
        $applications = $this->applicationService->getApplicationByStage('under_review');
        return response()->json($applications, 200);
    }

    public function get_approved_by_reviewer_Apps(){
        $applications = $this->applicationService->getApplicationByStage('approved_by_reviewer');
        return response()->json($applications, 200);
    }

    public function get_awaiting_payment_Apps(){
        $applications = $this->applicationService->getApplicationByStage('awaiting_payment');
        return response()->json($applications, 200);
    }

    public function get_approved_Apps(){
        $applications = $this->applicationService->getApplicationByStage('approved');
        return response()->json($applications, 200);
    }

    public function get_rejected_Apps(){
        $applications = $this->applicationService->getApplicationByStage('rejected');
        return response()->json($applications, 200);
    }


}
