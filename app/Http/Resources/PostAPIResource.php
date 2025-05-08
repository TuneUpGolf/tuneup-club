<?php
namespace App\Http\Resources;

use App\Models\Follower;
use App\Models\LikePost;
use App\Models\PurchasePost;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class PostAPIResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $user        = $this->isFollowerPost == true ? new FollowerAPIResource(Follower::find($this->follower->id)) : new influencerAPIResource(User::find($this->influencer->id));
        $likes       = LikePost::where('post_id', $this->id)->count();
        $apiResource = [
            'id'              => $this->id,
            'user'            => $user,
            'title'           => $this->title,
            'description'     => $this->description,
            'slug'            => $this->slug,
            'price'           => $this->price,
            'likes'           => $likes,
            'file'            => asset('/storage' . '/' . tenant('id') . '/' . $this->file),
            'subscribed_post' => $this->paid,
            'is_active'       => $this->status === 'active' ? true : false,
            "created_at"      => $this->created_at,
            'reports'         => $this->reportPost,

        ];
        if (Auth::user()->type === Role::ROLE_FOLLOWER && $this->paid) {
            $apiResource += (['post_purchase_status' => (bool) Auth::user()?->purchasePost->firstWhere('post_id', $this->id)?->active_status]);
        }

        return $apiResource;
    }
}
