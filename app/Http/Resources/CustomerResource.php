<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'address' => $this->address,
            'store_name' => $this->store_name,
            'store_address' => $this->store_address,
            'city' => $this->city,
            'state' => $this->state,
            'store_photo' => $this->store_photo,
            'owner_photo' => $this->owner_photo,
        ];
    }
}
