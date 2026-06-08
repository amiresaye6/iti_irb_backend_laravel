<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
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
          'full_name'    => ['required', 'string', 'min:10', 'max:255'],
            
            'email'        => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            
            'password'     => ['required', 'confirmed', Password::min(8)], 
            
            'national_id'  => ['required', 'string', 'regex:/^[0-9]{14}$/', 'unique:users,national_id'], 
            
            'phone_number' => ['required', 'string', 'size:11', 'regex:/^01[0125][0-9]{8}$/'],
            'faculty'      => ['required', 'string', 'max:255'],
            'department'   => ['required_if:role,student', 'string', 'max:255'],
            
            'id_front'     => ['required_if:role,student', 'file', 'image', 'mimes:jpeg,jpg,png,pdf', 'max:2048'],
            'id_back'      => ['required_if:role,student', 'file', 'image', 'mimes:jpeg,jpg,png,pdf', 'max:2048'],
            'role'         => ['sometimes', 'string','in:admin,reviewer,manager,super_admin,student'],
        ];
    }
    public function messages(): array
    {
        return [
            'full_name.min'    => 'الاسم بالكامل يجب أن يكون أكثر من 10 أحرف',
            'email.email'      => 'البريد الإلكتروني غير صالح',
            'email.unique'     => 'البريد الإلكتروني مسجل من قبل',
            'password.min'     => 'كلمة المرور يجب أن لا تقل عن 8 رموز',
            'password.confirmed' => 'كلمة المرور غير متطابقة مع تأكيد كلمة المرور',
            'national_id.regex'=> 'رقم البطاقة يجب أن يتكون من 14 رقم',
            'national_id.unique'=> 'رقم البطاقة مسجل من قبل',
            'id_front.mimes'   => 'صورة وجه البطاقة يجب أن تكون JPG أو PNG فقط',
            'id_back.mimes'    => 'صورة ظهر البطاقة يجب أن تكون JPG أو PNG فقط',
            'id_front.required'=> 'صورة وجه البطاقة مطلوبة',
            'id_back.required' => 'صورة ظهر البطاقة مطلوبة',
        ];
    }
}
