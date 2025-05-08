<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SlotAPIResource extends JsonResource
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
            'lesson_id' => $this->lesson_id,
            'lesson' => $this->when(
                request()->has('include_lesson'),
                fn() => new LessonAPIResource($this->lesson),
            ),
            'date_time' => $this->date_time,
            'location' => $this->location,
            'is_completed' => (bool) $this->is_completed,
            'is_active' => (bool) $this->is_active,
            'cancelled' => (bool) $this->cancelled,
            'tenant_id' => $this->tenant_id,
            'is_fully_booked' => (bool)$this->isFullyBooked(),
            'available_seats' => $this->availableSeats(),
            'is_package_lesson' => (bool) $this->lesson->is_package_lesson,
            'students' => $this->when(
                request()->has('include_students'),
                fn() => $this->follower->map(function ($student) {
                    return [
                        'id' => $student->id,
                        'name' => $student->name,
                        'email' => $student->email,
                        'phone' => $student->phone,
                        'dp' => asset('/storage' . '/' . tenant('id') . '/' . $student->dp),
                        'is_guest' => (bool) $student->pivot->isFriend,
                        'friend_name' => $student->pivot->isFriend ? $student->pivot->friend_name : null,
                    ];
                })
            ),
        ];
    }
}
