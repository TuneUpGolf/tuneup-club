<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AnnotationVideoApiResource extends JsonResource
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
            'id'            => $this->id,
            'uuid'          => $this->uuid,
            'influencer_id' => $this->influencer_id,
            'video_link'    => asset('/storage' . '/' . tenant('id') . '/' . $this->video_url),
        ];
    }
}
