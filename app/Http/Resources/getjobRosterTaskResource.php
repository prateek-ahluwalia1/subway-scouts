<?php

namespace App\Http\Resources;

use App\Models\Site;
use Illuminate\Http\Resources\Json\JsonResource;

class getjobRosterTaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {

        // 'https://appapi.thescouts.com.au/uploads/'

        // $customer_id = '';

        $baseURL = "https://appapi.thescouts.com.au/uploads/";
        $imageURLs = [];
        $taskEndImages = json_decode(json_decode($this->task_end_imgs, true), true);

            if (is_array($taskEndImages)) {


                foreach ($taskEndImages as $key => $value) {
                    $newObject = new \stdClass();
                    $newObject->imgPath = $baseURL.$value['imgPath'];
                    $newObject->timestamp = $value['timestamp'];
                    $imageURLs[] = $newObject;
                }
                // $imageURLs = array_map(function ($filename) use ($baseURL) {
                //     return $baseURL . $filename['imgPath'];
                // }, $taskEndImages);
                
                //  $imageURLs;
            } else {
                // Handle the case where JSON decoding failed or produced unexpected data.
                // You can return an empty array or some other default value as needed.
                $imageURLs = [];
            }

            // if(!empty($this->shift->site_id)){
            //     $s = Site::where('id', $this->shift->site_id)->first();
            //     $customer_id = $s->customer_id;
            // }



        return[
            'id' => $this->id,
            'task' => $this->task,
            'images' =>  $imageURLs,
            'task_start' => !empty($this->task_start) ?  usaToAusDateTime($this->task_start) : '',
            'task_end' => !empty($this->task_end) ? usaToAusDateTime($this->task_end) : '',
            // 'shift_start' => !empty($this->shift) ? usaToAusDateTime($this->shift->start) : '',
            // 'shift_end' => !empty($this->shift) ? usaToAusDateTime($this->shift->end) : '',
            'actual_start_time' => $this->start_time,
            'actual_end_time' => $this->end_time,
            'status' => !empty($this->status) ? $this->status : '',
            'start_location' => !empty($this->start_location) ? $this->start_location : '',
            'end_location' => !empty($this->end_location) ? $this->end_location : '',
            'note' => !empty($this->note) ? $this->note : '',
            //'staff_name' => !empty($this->shift) && !empty($this->shift->guard_id) ? getGuardName($this->shift->guard_id) : null,
            //'location_name' => !empty($this->shift) && !empty($this->shift->site_id) ? getSiteName($this->shift->site_id) : null,
            //'customer_name' => !empty($customer_id) ? getCustomerName($customer_id) : null,
        ];
    }
}
