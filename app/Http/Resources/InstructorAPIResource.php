<?php
namespace App\Http\Resources;

use App\Models\Follow;
use Illuminate\Http\Resources\Json\JsonResource;

class InstructorAPIResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $followers = Follow::where('influencer_id', $this->id)->where('active_status', 1)->where('follower_id', '!=', null);

        return [
            'id'                  => $this->id,
            'uuid'                => $this->uuid,
            'name'                => $this->name,
            'email'               => $this->email,
            'type'                => $this->type,
            'dial_code'           => $this->dial_code,
            'followers'           => $followers->count(),
            'subscribers'         => $followers->where('isPaid', 1)->count(),
            'experience'          => (int) $this->experience,
            'address'             => $this->address,
            'country'             => $this->country,
            'golf_course'         => $this->golf_course,
            'phone'               => $this->phone,
            'bio'                 => $this->bio,
            'avg_rate'            => $this->avg_rate,
            'is_stripe_connected' => $this->is_stripe_connected,
            'dp'                  => asset('/storage' . '/' . tenant('id') . '/' . $this->logo),
            'sub_price'           => $this->sub_price,
            'push_token'          => $this->pushToken?->token,
            'social_url_ig'       => $this->social_url_ig,
            'reports'             => $this->when(
                request()->has('include_reports'),
                fn() => $this->reportUser,
            ),
            'created_at'          => $this->created_at,
        ];
    }
}
