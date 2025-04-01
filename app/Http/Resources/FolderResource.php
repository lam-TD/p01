<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FolderResource extends JsonResource
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
            'path' => $this->path,
            'parent_id' => $this->parent_id,
            'parent' => $this->when($this->parent_id && $this->parent, new FolderResource($this->parent)),
            'created_by' => $this->created_by,
            'creator' => $this->when($this->creator, [
                'id' => $this->creator->id,
                'name' => $this->creator->name,
            ]),
            'metadata' => $this->metadata,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'deleted_at' => $this->deleted_at ? $this->deleted_at->toIso8601String() : null,
            'links' => [
                'self' => route('api.folders.show', $this->id),
                'files' => route('api.files.index', ['folder_id' => $this->id]),
            ],
        ];
    }
}
