<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShareTenderRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'recipient_name' => 'required|string',
            'projectName' => 'required',
            'documents.*' => 'required|mimes:pdf,docx',
            'email' => 'required|email',
        ];
    }
}
