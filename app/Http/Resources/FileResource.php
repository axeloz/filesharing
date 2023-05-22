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
			'uuid'					=> $this->uuid,
			'bundle_slug'			=> $this->bundle_slug,
			'original'				=> $this->original,
			'filesize'				=> (int)$this->filesize,
			'fullpath'				=> $this->fullpath,
			'filename'				=> $this->filename,
			'created_at'			=> $this->created_at,
			'status'				=> $this->status,
			'hash'					=> $this->hash
		];
    }
}
