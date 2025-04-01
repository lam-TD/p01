<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FileResource extends JsonResource
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
            'original_name' => $this->original_name,
            'size' => $this->size,
            'human_size' => $this->getHumanReadableSize(),
            'mime_type' => $this->mime_type,
            'extension' => $this->extension,
            'folder_id' => $this->folder_id,
            'folder' => $this->when($this->folder, new FolderResource($this->folder)),
            'uploaded_by' => $this->uploaded_by,
            'uploader' => $this->when($this->uploader, [
                'id' => $this->uploader->id,
                'name' => $this->uploader->name,
            ]),
            'status' => $this->status,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'deleted_at' => $this->deleted_at ? $this->deleted_at->toIso8601String() : null,
            'links' => [
                'self' => route('api.files.show', $this->id),
                'download' => route('api.files.download', $this->id),
            ],
        ];
    }

    /**
     * Get human readable file size.
     */
    protected function getHumanReadableSize(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2).' '.$units[$i];
    }
}
