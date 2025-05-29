<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class BannerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $url = Storage::disk('s3')->url(
            str_ends_with($this->path, '.gif')
                ? $this->path
                : str_replace('.png', '.webp', $this->path)
        );

        return [
            'id' => $this->id,
            'url' => $url,
        ];
    }
}
