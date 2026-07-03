<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RunSheetJobRosterShiftsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $RunSheetJobRoster = round(collect($this->RunSheetJobRoster)->sum('hours'), 2);
        return [
            'run_sheet_id' => $this->id,
            'run_sheet_description' => !empty($this->description) ? $this->description : '',
            'run_sheet_title' => !empty($this->title) ? $this->title : '',
            'customer_id' => $this->customer_id,
            // 'customer_name' => $this->customer->name,
            //'count' => $this->count,
            'RunSheetJobRoster' =>  RunSheetJobsResource::collection($this->RunSheetJobRoster),
            'jobRosterSum' => $RunSheetJobRoster,
        ];
    }
}
