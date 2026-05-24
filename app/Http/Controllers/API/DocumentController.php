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

    public function store($file,$type,$application_id)
    {
        return $this->documentationService->store($file,$type,$application_id);
    }

    public function show($id){
        $docs = $this->documentationService->getDocById($id);
        return response()->json($docs, 200);

    }

    public function getDocsByAppId($app_id)
    {
        $docs = $this->documentationService->getDocsByAppId($app_id);
        return response()->json($docs, 200);
    }

    public function destroy($id){
        $doc = $this->documentationService->deleteDoc($id);
        return response()->json($docs, 200);
    }

}
