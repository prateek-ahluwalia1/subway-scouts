<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreJobRosterRequest extends FormRequest
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
            'roster_name' => 'required',
            'start' => 'required',
            'end' => 'required',
            'customer_id' => 'required',
            'site_id' => 'required',
            'user_id' => 'required',
        ];
    }
}
