<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
class SkuResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $url = Storage::disk('s3')->url(str_replace('.png', '.webp', $this->image->path));
        return [
            'id' => $this->id,
            'name' => $this->name,
            'packaging' => $this->packaging,
            'description' => $this->description,
            'image' => $url,
            'brand' => $this->product->name,
            'category' => $this->product->category->name,
            'file' => $this->product->file ? asset('storage/'.$this->product->file) : null,
        ];
    }
}
