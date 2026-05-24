<?php

namespace App\Http\Services;

use App\Models\Application;
use App\Models\Document;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class DocumentService
{
    public function store($file,$type,$application_id){
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
}