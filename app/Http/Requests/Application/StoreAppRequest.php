<?php

namespace App\Http\Requests\Application;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreAppRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "title" => "required|string|min:3|max:255",
            "principal_investigator" => "required|string|min:3",
            "co_investigators" => "required|string|min:3",
            "keywords" => "required|string|min:3",
            "protocol_review_app" => "required|file|mimes:pdf,doc,docx|max:4096",
            "oral_presentaion" => "required|file|mimes:pdf,doc,docx|max:4096",
            "pi_consent" => "required|file|mimes:pdf,doc,docx|max:4096",
            "research_procedures_approval" => "required|file|mimes:pdf,doc,docx|max:4096",
            "conflict_of_interest" => "required|file|mimes:pdf,doc,docx|max:4096",
            "patient_consent" => "required|file|mimes:pdf,doc,docx|max:4096",
            "research_alignment_with_research_plan" => "required|file|mimes:pdf,doc,docx|max:4096",
            "research_protocol" => "required|file|mimes:pdf,doc,docx|max:10240",
        ];
    }
}
