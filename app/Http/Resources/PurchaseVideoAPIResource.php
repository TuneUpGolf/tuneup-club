<?php

namespace App\Http\Resources;

use App\Http\Controllers\Admin\PurchaseController;
use App\Models\FeedbackContent;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class PurchaseVideoAPIResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $feedbackContent = $this->feedbackContent;
        foreach ($feedbackContent as $content) {
            $content->url  = asset('/storage' . '/' . tenant('id') . '/' . $content->url);
        }
        return [
            'id' => $this->id,
            'purchase_id' => $this->purchase_id,
            'feedback' => $this->feedback,
            'note' => $this->note,
            'video_link' => asset('/storage' . '/' . tenant('id') . '/' . $this->video_url),
            'video_link_2' => isset($this->video_url_2) ? asset('/storage' . '/' . tenant('id') . '/' . $this->video_url_2) : null,
            'instructor_feedback' => $feedbackContent,
            'thumbnail' => asset('/storage' . '/' . tenant('id') . '/' . $this->thumbnail),
        ];
    }
}
