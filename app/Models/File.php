<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
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


	public function getKeyName()
	{
		return 'uuid';
	}

	public function getIncrementing()
	{
		return false;
	}

	public static function schema(Blueprint $table)
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

	public function bundle() {
		return $this->belongsTo(Bundle::class);
	}
}
