<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateTenderRequest extends FormRequest
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
    public function rules()
    {
        return [


            'project_id' => 'required',

            'project_name' => 'required|string',
            'validity_start'  => 'required|date',
            'validity_end'  => 'required|date',
            'company' => 'required|string',
            'project_title' => 'required|string',
            'prepared_by' => 'required|string',
            'location' => 'required|string',
            'customer' => 'required|string',
            'date' => 'required|date',
            'conditions' => 'required|string',
            'boq' => 'string',
            'documents.*' => 'required|mimes:pdf,docx',

        ];
    }
}
