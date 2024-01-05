<?php

namespace App\Http\Resources;

use App\Helpers\Auth;
use App\Helpers\Upload;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

use App\Http\Resources\UserResource;
use App\Http\Resources\FileResource;

class BundleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
		/**
		Do not return private data on the preview page
		 */
		$full = false;
		if (Auth::isLogged() || Upload::canUpload($request->ip())) {
			$full = true;
		}

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
			'password'			=> $this->when($full === true, $this->password),
			'owner_token'		=> $this->when($full === true, $this->owner_token),
			'preview_token'		=> $this->when($full === true, $this->preview_token),
			'deletion_link'		=> $this->when($full === true, $this->deletion_link),
			'user'				=> $this->when($full === true, new UserResource($this->user))
		];

		return $response;
	}
}
