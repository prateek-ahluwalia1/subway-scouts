<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ShowGuardFeedBackResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return[
         'id'  => $this->id,
         'admin_name' => $this->admin->name,
         'admin_image' => returnImgPath('admin',$this->admin->image),
         'feedback' => $this->feedback,
         'created_at' => strtotime($this->created_at),
         'updated_at' => ($this->on_edit != '' && $this->on_edit != null) ?  strtotime($this->updated_at) : '',
         'on_edit' => ($this->on_edit != '' && $this->on_edit != null) ?  'Edited' : '',
        ];
    }
}
