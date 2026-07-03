<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BeforeUpdateScrumboardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {

        $originalArray = json_decode($this->tags, true);
        $keyValueArray = [];
        foreach ($originalArray as $item) {
            $tag = $item['tag'];
            if (isset($keyValueArray['tag'])) {
                $keyValueArray['tag'] .= ',' . $tag;
            } else {
                $keyValueArray['tag'] = $tag;
            }
        }


        return [
            'Title' => $this->title,
            'Description' => $this->description,
            'tags' => $keyValueArray,
        ];
    }
}
