<?php

namespace App\Http\Resources\Job;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Helpers\HttpStatus;
use App\Http\Resources\User\UserResource;

class JobRosterResource extends JsonResource {

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

            'id' => $this->roster_id,
            'event_id' =>  $this->event_id,
            'guard_id' => $this->guard_id,
            'job_id' => $this->site_id,
            'start' => $this->start,
            'end' => $this->end,
            'temp_date' => $this->temp_date,
            'temp_start' => $this->temp_start,
            'temp_end' => $this->temp_end,
            'publish_status' => $this->publish_status,
            'add_status' => $this->add_status,
            'job_status' => $this->job_status,
            'job' => $this->jobTemplate($this->job),
            'guard' => new  UserResource($this->guards)
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
