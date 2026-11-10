<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class BundleResourceFiltered extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
		$response = [
			'created_at' 		=> $this->created_at,
			'completed'			=> (bool)$this->completed,
			'expiry'			=> (int)$this->expiry,
			'expires_at'		=> $this->expires_at,
			'slug'				=> $this->slug,
			'fullsize'			=> (int)$this->fullsize,
			'title'				=> $this->title,
			'description'		=> $this->description,
			'description_html'	=> ! empty($this->description) ? Str::markdown($this->description) : null,
			'max_downloads'		=> (int)$this->max_downloads,
			'downloads'			=> (int)$this->downloads,
			'files'				=> FileResource::collection($this->files),
			'preview_link'		=> $this->preview_link,
			'download_link'		=> $this->download_link,
		];

		return $response;
	}
}
