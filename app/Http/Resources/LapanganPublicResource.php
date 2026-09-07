<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LapanganPublicResource extends JsonResource
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
            'nama' => $this->nama,
            'harga_per_jam' => $this->harga_per_jam,
            'tipe_lapangan' => $this->tipe_lapangan,
            'status' => $this->status,
            'slug' => $this->slug,
        ];
    }
}
