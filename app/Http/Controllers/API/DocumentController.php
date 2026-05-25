<?php

namespace App\Http\Controllers\API;
use App\Http\Services\DocumentService;

use App\Http\Controllers\Controller;

class DocumentController extends Controller
{
    public function __construct(DocumentService $documentationService)
    {
        $this->documentationService = $documentationService;
    }

    // store document
    public function store($file,$type,$application_id)
    {
        return $this->documentationService->store($file,$type,$application_id);
    }

    // show document by id
    public function show($id){
        $docs = $this->documentationService->getDocById($id);
        return response()->json($docs, 200);

    }

    // get documents for specific app
    public function getDocsByAppId($app_id)
    {
        $docs = $this->documentationService->getDocsByAppId($app_id);
        return response()->json($docs, 200);
    }

    // delete documentaion by id
    public function destroy($id){
        $doc = $this->documentationService->deleteDoc($id);
        return response()->json($docs, 200);
    }

}
