<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RunSheetJobRosterShiftsByGuardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $RunSheetJobRoster = collect($this->RunSheetJobRoster)->sum('hours');
        return [
            'guard_id' => $this->id,
            'first_name' => $this->first_name,
            'email' => $this->email,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'profile_image' => returnImgPath('guard',$this->profile_image),
            'RunSheetJobRoster' =>  RunSheetJobsResource::collection($this->RunSheetJobRoster),
            'jobRosterSum' => $RunSheetJobRoster,
        ];
    }
}
