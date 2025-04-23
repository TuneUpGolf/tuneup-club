<?php

namespace App\Http\Resources;

use App\Models\RequestDomain;
use Illuminate\Http\Resources\Json\JsonResource;

class TenantAPIResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $domain = \DB::table('domains')->selectRaw('*')
            ->where('tenant_id', $this->tenant_id)
            ->first();
        return [
            'name' => $this->name,
            'email' => $this->email,
            'tenant_id' => $this->tenant_id,
            'type' => $this->type,
            'dial_code' => $this->dial_code,
            'phone' => $this->phone,
            'bio' => $this->bio,
            'logo' => asset('/storage' . '/' . tenant('id') . '/' . $this->logo),
            'created_at' => $this->created_at,
            'domain' => isset($domain->actual_domain) ? $domain->actual_domain : "",
            'service_fee' => $this->service_fee,
        ];
    }
}
