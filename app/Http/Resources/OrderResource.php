<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'sales' => new UserResouce($this->user),
            'customer' => new CustomerResource($this->customer),
            'shipper' => new UserResouce($this->driver),
            'items' => OrderItemResource::collection($this->orderItems),
            'payments' => $this->whenLoaded('payments', OrderPaymentResource::collection($this->payments)),
            'quantity' => (int)$this->orderItems->sum('quantity'),
            'total_price' => (int)$this->orderItems->sum('total'),
            'status' => $this->status,
            'paid' => (int)$this->payments->sum('amount'),
            'remaining' => (int)$this->payments->first()->remaining,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
