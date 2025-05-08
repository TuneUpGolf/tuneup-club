<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FollowerAPIResource extends JsonResource
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
            'name'          => $this->name,
            'email'         => $this->email,
            'type'          => $this->type,
            'dial_code'     => $this->dial_code,
            'phone'         => $this->phone,
            'bio'           => $this->bio,
            'push_token'    => $this->pushToken?->token,
            'social_url_ig' => $this->social_url_ig,
            'dp'            => asset('/storage' . '/' . tenant('id') . '/' . $this->dp),
            'created_at'    => $this->created_at,
        ];
    }
}
