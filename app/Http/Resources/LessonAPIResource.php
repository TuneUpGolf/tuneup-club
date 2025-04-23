<?php
namespace App\Http\Resources;

use App\Models\Purchase;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class LessonAPIResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $purchaseQuantity = Purchase::where('lesson_id', $this->id);

        return [
            "lesson_id"          => $this->id,
            "lesson_name"        => $this->lesson_name,
            "lesson_price"       => $this->lesson_price,
            "lesson_quantity"    => $this->lesson_quantity,
            "required_time"      => $this->required_time,
            "duration"           => $this->lesson_duration,
            "lesson_type"        => $this->type,
            "is_package_lesson"  => (bool) $this->is_package_lesson,
            "payment_method"     => $this->payment_method,
            'totalPurchases'     => $purchaseQuantity->count(),
            'completedPurchases' => $purchaseQuantity->where('status', Purchase::STATUS_COMPLETE)->count(),
            "instructor"         => $this->when(
                request()->has('include_instructor'),
                fn() => new InstructorAPIResource(User::find($this->created_by)),
            ),
            "logo"               => asset('/storage' . '/' . tenant('id') . '/' . $this->logo_image),
            "banner"             => asset('/storage' . '/' . tenant('id') . '/' . $this->banner_image),
            "lesson_description" => $this->lesson_description,
            "slots"              => $this->when(
                request()->has('include_slots'),
                fn() => SlotAPIResource::collection($this->slots),
            ),
            "max_students"       => $this->max_students,
            "created_at"         => $this->created_at,
        ];
    }
}
