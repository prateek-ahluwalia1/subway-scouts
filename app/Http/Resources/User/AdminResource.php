<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Helpers\HttpStatus;

class AdminResource extends JsonResource {

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

        // $first_name = $this->first_name;
        // $middle_name = $this->middle_name;
        // $last_name = $this->last_name;

        // if (!empty($middle_name)) {
        //     $full_name = $first_name . ' ' . $middle_name . ' ' . $last_name;
        // } else {
        //     $full_name = $first_name . ' ' . $last_name;
        // }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'profile_image' => $this->profile_image != '' ? env('ASSET_URL').'admin/'.$this->profile_image : "",
            // 'profile_image' => $this->profile_image != '' ? 'https://'.request()->getHttpHost().'/uploads/'.$this->profile_image : "",
            'userType' => $this->userType,
            'auth_token' => $this->auth_token,
            'state' => $this->state,
            'is_online' => $this->is_online,
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
