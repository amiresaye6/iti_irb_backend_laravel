<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

class DocumentController extends Controller
{
    public function store($file,$application_id){
        // generate unique file name
        $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();

        // store file in storage/app/public/documentations
        $path = $file->storeAs('documentations', $fileName, 'public');

        Document::create([
            'application_id' => $application_id,
            'document_type' => $file,
            'file_path' => $path
        ]);
    }
}
