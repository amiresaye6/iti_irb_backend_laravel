<?php

namespace App\Http\Requests\Application;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class EditAppRequest extends FormRequest
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
            "protocol_review_app" => "file|mimes:pdf,doc,docx|max:4096",
            "oral_presentaion" => "file|mimes:pdf,doc,docx|max:4096",
            "pi_consent" => "file|mimes:pdf,doc,docx|max:4096",
            "research_procedures_approval" => "file|mimes:pdf,doc,docx|max:4096",
            "conflict_of_interest" => "file|mimes:pdf,doc,docx|max:4096",
            "patient_consent" => "file|mimes:pdf,doc,docx|max:4096",
            "research_alignment_with_research_plan" => "file|mimes:pdf,doc,docx|max:4096",
            "research_protocol" => "file|mimes:pdf,doc,docx|max:10240",
        ];
    }
}
