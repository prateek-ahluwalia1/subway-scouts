<?php

namespace App\Http\Resources\Notification;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Helpers\HttpStatus;
use App\Http\Resources\Notification\NotificationResource;
use Illuminate\Support\Facades\DB;


class NotificationResource extends JsonResource {

    private $count;
    private $message;
    private $statusCode = HttpStatus::STATUS_OK;

    public function __construct($resource) {
        parent::__construct($resource);
        $this->count = 1;
        if ($this->count == 0) {
            $this->message = 'No Notification Found.';
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
            //'page' =>  $this->page,
            'title' => $this->title,
            'message' => $this->message,
            'notification_to' => $this->send_to,
            'notification_type' => $this->type,
            'date_added' => $this->send_time,
            'TIMESTAMP_INSERTED' => $this->created_at,
            'TIMESTAMP_LAST_UPDATED' => $this->updated_at,
            'user_id' => $this->user_id,
            'user_name' => $this->user_name,
            'user_image' => $this->user_image,
            'user_is_online' => $this->user_is_online,
            
        ];
    }

    public function with($request) {
        // if (!$this->resource) {
        //     return [];
        // }
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
