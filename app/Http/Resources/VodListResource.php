<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VodListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'title' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'playback_url' => route('api.vods.playback', ['id' => $this->slug]),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
