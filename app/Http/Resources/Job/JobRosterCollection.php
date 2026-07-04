<?php

namespace App\Http\Resources\Job;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Helpers\HttpStatus;
use Carbon\Carbon;

class JobRosterCollection extends ResourceCollection {

    private $count;
    private $message;
    private $statusCode = HttpStatus::STATUS_OK;
    private $request_data;

    public function __construct($resource) {
        parent::__construct($resource);
        $this->count = $this->count();
        $this->request = $_POST;
        if ($this->count == 0) {
            $this->message = 'No Record Found.';
        }else{
            $this->message = 'Data Received';
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
        $week_start = strtotime(Carbon::now()->startOfWeek());
        $week_end = strtotime(Carbon::now()->endOfWeek());
        if(isset($this->request['week_no'])){
            $week = $this->request['week_no'];
            if($week != 0){
                $startofweek = Carbon::now()->startOfWeek();
                $endofweek = Carbon::now()->endOfWeek();
                $startofweek = strtotime($startofweek) + (60*60*24*7 * $week) + 3600;
                $endofweek = strtotime($endofweek) + (60*60*24*7 * $week)-3600;
                $week_start = $startofweek;
                $week_end = $endofweek;
                }
        }
        return [
            'message' => $this->message,
            'start_date' => date('Y-m-d H:i:s', $week_start),
            'end_data' => date('Y-m-d H:i:s', $week_end),
            'now' => Carbon::now()->startOfWeek(),
            'end' => Carbon::now()->endOfWeek(),
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
