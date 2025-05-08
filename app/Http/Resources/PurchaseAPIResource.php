<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseAPIResource extends JsonResource
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
            'lesson' => new LessonAPIResource($this->lesson),
            'total_amount' => $this->total_amount,
            'student' => $request->student_request ? new StudentAPIResource($this->student) : null,
            'payment_status' => $this->status,
            'isFeedbackComplete' => (bool)$this->isFeedbackComplete,
            'created_at' => $this->created_at,
        ];
    }
}
