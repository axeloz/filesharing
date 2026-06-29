<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use \Orbit\Concerns\Orbital;
use Illuminate\Database\Schema\Blueprint;

class File extends Model
{
	use Orbital;

	public $fillable = [
		'uuid',
		'bundle_slug',
		'original',
		'filesize',
		'fullpath',
		'filename',
		'created_at',
		'status',
		'hash'
	];

	public $incrementing = false;


	public function getKeyName(): string
	{
		return 'uuid';
	}

	public function getIncrementing(): bool
	{
		return false;
	}

	public static function schema(Blueprint $table): void
	{
		$table->string('uuid');
		$table->string('original')->nullable();
		$table->string('filename')->nullable();
		$table->string('status')->nullable();
		$table->string('hash')->nullable();
		$table->longText('fullpath')->nullable();
		$table->boolean('filesize')->nullable();
		$table->string('bundle_slug');
	}

	public function bundle(): BelongsTo {
		return $this->belongsTo(Bundle::class);
	}
}
