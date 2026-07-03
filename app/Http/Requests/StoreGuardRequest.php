<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGuardRequest extends FormRequest
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
        return [
            // 'first_name' => 'required',
            // 'middle_name' => 'required',
            // 'last_name' => 'required',
            'email' => 'required|email|unique:guards',
            //'password' => 'required',
            // 'phone' => 'required',
            // 'address' => 'required',           
            // 'coordinates' => 'required',           
            // 'city' => 'required',           
            // 'state' => 'required',           
            // 'postal_code' => 'required',           
            // 'dob' => 'required',           
            // 'gender' => 'required',           
            // 'emergency_contact_name' => 'required',           
            // 'registration_type' => 'required',                      
            // 'guard_status' => 'required',         
        ];
    }
}
