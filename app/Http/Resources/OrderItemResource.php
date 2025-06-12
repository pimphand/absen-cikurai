<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
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
            'brand' => $this->sku->product->name,
            'name' =>  $this->sku->name,
            'quantity' => (int)$this->quantity,
            'total' => (int)$this->total,
            'price' => (int)$this->price,
            'returns' => (int)$this->returns,
            'discount' => (int)$this->discount,
        ];
    }
}
