<?php

namespace App\Http\Resources\Job;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Helpers\HttpStatus;
use App\Http\Resources\User\UserResource;

class JobResource extends JsonResource {

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
            $this->message = 'Data Received.';
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
            'booking_id' =>  $this->booking_id,
            'level' => $this->level,
            'state' => $this->state,
            'stateType' => $this->stateType,
            'payrol' => $this->payrol,
            'details' => $this->details,
            'sos_phone' => $this->sos_phone,
            'start' => date('d-m-Y H:i', strtotime($this->temp_start)),
            'end' => date('d-m-Y H:i', strtotime($this->temp_end)),
            'address' => $this->address,
            'site_name' => $this->site_name,
            'site_decription' => $this->site_decription,
            'coordinates' => $this->coordinates,
            'petrol_site' => $this->petrol_site,
            'guards_count' => $this->guards_count,
            'hourly_rate' => $this->hourly_rate,
            'job_status' => $this->job_status,
            'date_added' => $this->date_added,
            'TIMESTAMP_INSERTED' => $this->TIMESTAMP_INSERTED,
            'TIMESTAMP_LAST_UPDATED' => $this->TIMESTAMP_LAST_UPDATED,
            'customer' => new UserResource($this->customer),
            'contractor' => new  UserResource($this->contractor),
            'job_roster' => JobRosterResource::collection($this->jobRoster)
        ];
    }

    public function with($request) {
        if (!$this->resource) {
            return [];
        }
        return [
            'message' => $this->message,
            'status' => HttpStatus::STATUS_OK_LABEL,
            'code'  => HttpStatus::STATUS_OK,
            'meta' => [
                'host_url' => url('/'),
                'total' => $this->count,
                'request_time' => time()
            ],
        ];
    }

    public function withResponse($request, $response) {
        $response->setStatusCode($this->statusCode);
    }
}
