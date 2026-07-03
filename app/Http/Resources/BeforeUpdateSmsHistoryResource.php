<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BeforeUpdateSmsHistoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'Admin Name' => !empty($this->admin_id) ? getAdminName($this->admin_id) : 'N/A',
            'Message Body' => !empty($this->msg_body) ? $this->msg_body  :  'N/A',
            'To' => !empty($this->to) ? $this->to  :  'N/A',
            'To Number' => $this->to_number,
            'From Number' => $this->from_number,
            'Message ID' => $this->message_id,
            'Original Message ID' => $this->original_message_id,
            'Date' => !empty($this->datetime) ? timeStampToAus($this->datetime) : 'N/A',
            'User ID' => !empty($this->user_id) ? $this->user_id : 'N/A',
            'Direction' => !empty($this->direction) ? $this->direction : 'N/A',
            'Seen Status' => !empty($this->seen_status) ? $this->seen_status : 'N/A',
            'Created At' => !empty($this->created_at) ? usaToAusTime($this->created_at) : 'N/A',
        ];
    }
}
