<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SkuResource extends JsonResource
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
            'packaging' => $this->packaging,
            'description' => $this->description,
            'image' => $this->image->path ? str_replace('.png', '.webp', $this->image->path) : null,
            'brand' => $this->product->name,
            'category' => $this->product->category->name,
            'file' => $this->product->file ? asset('storage/'.$this->product->file) : null,
        ];
    }
}
