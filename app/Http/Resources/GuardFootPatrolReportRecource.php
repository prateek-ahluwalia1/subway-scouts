<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GuardFootPatrolReportRecource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $photos = [];

        foreach (json_decode($this->photo) as $key => $value) {
            $newObject = new \stdClass();
            $newObject->imgPath = returnImgPathCheck('uploads', $value->imgPath );
            $newObject->timestamp = $value->timestamp;
            $photos[] = $newObject;
        }
        return [
            'id' => $this->id,
            'site_name' => !empty($this->site_name) ? $this->site_name : '',
            'date' => !empty($this->date) ? $this->date : '',
            'time' => !empty($this->time) ? $this->time : '',
            'pdf' => !empty($this->pdf) ? $this->pdf : '',
            'patrolling_details' => !empty($this->patrolling_detail) ? $this->patrolling_detail : '',
            'signature' => !empty($this->signature) ? returnImgPathCheck('uploads', $this->signature) : '',
            'photo' => !empty($photos) ? $photos : '',

        ];
    }
}
