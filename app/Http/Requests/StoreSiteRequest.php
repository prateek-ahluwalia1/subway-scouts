<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSiteRequest extends FormRequest
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
            // 'customer_id' => 'required',
            // 'site_name' => 'required',
            // 'site_level' => 'required',
            // 'site_state' => 'required',
            // 'payrol' => 'required',
            // 'site_start_date' => 'required',
            //'site_end_date' => 'required',
            // 'signin_radius' => 'required',
            // 'radius_alert' => 'required',
        ];
    }
}
