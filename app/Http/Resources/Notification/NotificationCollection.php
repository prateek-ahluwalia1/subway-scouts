<?php

namespace App\Http\Resources\Notification;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Helpers\HttpStatus;

class NotificationCollection extends ResourceCollection {

    private $count;
    private $message;
    private $statusCode = HttpStatus::STATUS_OK;

    public function __construct($resource) {
        parent::__construct($resource);
        $this->count = $this->count();
        if ($this->count == 0) {
            $this->message = 'No notiifcation found.';
        }else{
            $this->message = 'Data Received.';
        }
    }

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request) {
        return [
            'data' => $this->collection
        ];
    }

    public function with($request) {
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
