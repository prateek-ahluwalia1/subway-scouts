<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Helpers\HttpStatus;

class CustomerResource extends JsonResource {

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
            'email' => $this->email,
            'profile_image' => "",
            'security_license' => "",
            'driver_license' => "",
            'visa' =>'',
            'passport' => '',
            'phone' => $this->phone,
            'address' => $this->address,
            'coordinates' => '',
            'city' => $this->city,
            'state' => $this->state,
            'postal_code' => $this->postal_code,
            'date_of_birth' => '',
            'gender' => '',
            'userType' => 'customer'
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
