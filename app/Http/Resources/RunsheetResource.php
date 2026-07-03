<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RunsheetResource extends JsonResource
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
            'id' => $this->id,
            'description' => $this->description,
            'title' => $this->title,
            'state' => $this->state,
            'patrol_brief_file' => $this->patrol_brief_file,
            'created_at' => !empty($this->created_at) ? usaToAus($this->created_at) : '',
            'run_sheet_details' => RunsheetDetailResource::collection($this->runSheetDetails),
        ];
    }
}
