<?php

namespace App\Http\Resources;

use App\Helper\HttpStatus;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    
    private $count;
    private $message;
    private $statusCode = HttpStatus::STATUS_OK;

    public function __construct($resource) {
        parent::__construct($resource);
        $this->count = 1;
        if ($this->count == 0) {
            $this->message = 'No user found.';
            $this->statusCode = HttpStatus::STATUS_NO_CONTENT;
        }else{
            $this->message = 'You are successfully login!';
        }
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request) {

        return [
            'id' => $this->id,
            'name' => $this->name,
            //'profile_image' => $this->profile_image != '' ? 'https://'.request()->getHttpHost().'/uploads/'.$this->profile_image : "",
            //'security_license' => $this->security_license_file != '' ? 'https://'.request()->getHttpHost().'/uploads/'.$this->security_license_file : "",
            //'security_license_back' => $this->security_license_file_back != '' ? 'https://'.request()->getHttpHost().'/uploads/'.$this->security_license_file_back : "",
            //'driver_license' => $this->driver_license_file != '' ? 'https://'.request()->getHttpHost().'/uploads/'.$this->driver_license_file : "",
            //'visa' => $this->visa_file != '' ? 'https://'.request()->getHttpHost().'/uploads/'.$this->visa_file : '',
            //'passport' => $this->passport_file != '' ? 'https://'.request()->getHttpHost().'/uploads/'.$this->passport_file : '',
            //'phone' => $this->phone,
            //'address' => $this->address,
            //'coordinates' => $this->coordinates,
            //'city' => $this->city,
            //'state' => $this->state,
            //'postal_code' => $this->postal_code,
            //'date_of_birth' => $this->dob,
            //'gender' => $this->gender,
            //'security_license_number' => $this->security_license_number,
            //'security_license_expiration' => $this->security_license_expiration,
            //'userType' => 'guard'
        ];
    }

    public function with($request) {
        if (!$this->resource) {
            return [];
        }
        return [
            'message' => $this->message,
            'auth_token' => $this->auth_token,
            'status' => HttpStatus::STATUS_OK_LABEL,
            'code'  => HttpStatus::STATUS_OK,
            'meta' => [
                'host_url' => url('/'),
                'full_url' => url('/'.HttpStatus::API_VERSION.'/login'),
                'total' => $this->count,
                'request_time' => time()
            ],
        ];
    }

    public function withResponse($request, $response) {
        $response->setStatusCode($this->statusCode);
    }
}
