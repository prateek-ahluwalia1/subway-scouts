<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        // return [
        //     'email' => 'required|email|unique:customers',
            
            
        // ];

        $rules = [
            'email' => 'required|email',
        ];
    
        // Check if the 'id' field is present in the request
        if (!$this->has('id')) {
            // If 'id' is not present, it's a new record, so apply the 'unique' rule
            $rules['email'] .= '|unique:customers,email';
        }
        return $rules;
    }
}
