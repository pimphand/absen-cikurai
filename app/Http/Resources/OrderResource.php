<?php

namespace App\Http\Resources;

use App\Models\Order;
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
            'collector' => new UserResouce($this->collector),
            'items' => OrderItemResource::collection($this->orderItems),
            'payments' => $this->whenLoaded('payments', OrderPaymentResource::collection($this->payments)),
            'quantity' => (int)$this->orderItems->sum('quantity'),
            'total_price' => $this->orderItems()->sum('total'),
            'status' => Order::Status($this->status),
            'paid' => (int)$this->payments->sum('amount'),
            'remaining' => (int)$this->payments->first()->remaining,
            'shipped_at' => $this->tanggal_pengiriman,
            'type_discount' => (bool)$this->type_discount,
            'discount' => (int)$this->discount,
            'note' => $this->note,
            'file' => $this->file,
            'bukti_pengiriman' => $this->bukti_pengiriman,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
