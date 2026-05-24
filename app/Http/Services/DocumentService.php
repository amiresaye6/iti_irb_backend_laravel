<?php

namespace App\Http\Services;

use App\Models\Application;
use App\Models\Document;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DocumentService
{
    public function store($file,$type,$application_id)
    {
        // generate unique file name
            $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();

            // store file in storage/app/public/documentations
            $path = $file->storeAs('documentations', $fileName, 'public');

            Document::create([
                'application_id' => $application_id,
                'document_type' => $type,
                'file_path' => $path
            ]);
    }

    public function storeDocs($validated, $application_id)
    {
        $documents = [
            'protocol_review_app',
            'oral_presentaion',
            'pi_consent',
            'research_procedures_approval',
            'conflict_of_interest',
            'patient_consent',
            'research_alignment_with_research_plan',
            'research_protocol'
        ];

        foreach ($documents as $type) {
            if (!isset($validated[$type])) continue;
            $file = $validated[$type];
            $this->store($file,$type,$application_id);
        }
        return;
    }

    public function getDocsByAppId($app_id)
    {
        $docs = Document::where('application_id',$app_id)->get();
        return $docs;
    }

    public function getDocById($id)
    {
        $doc = Document::where('id',$id)->get();
        return $doc;
    }

    public function deleteDoc($id)
    {
        $doc = Document::findOrFail($id);

        if (Storage::disk('public')->exists($doc->file_path)) {
            Storage::disk('public')->delete($doc->file_path);
        }

        $doc->delete();

        return true;
    }

}