<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Orbit\Concerns\Orbital;
use Illuminate\Database\Schema\Blueprint;

class Bundle extends Model
{
    use Orbital;

    public $incrementing = false;

    public $fillable = [
        'user_username',
        'created_at',
        'completed',
        'expiry',
        'expires_at',
        'password' ,
        'slug',
        'owner_token',
        'preview_token',
        'fullsize',
        'title',
        'description',
        'max_downloads',
        'downloads',
        'preview_link',
        'download_link',
        'deletion_link'
    ];

    protected $casts = [
        //'expires_at' => 'datetime:Y-m-d',
    ];

    public function getKeyName(): string
    {
        return 'slug';
    }

    public function getIncrementing(): bool
    {
        return false;
    }

    public static function schema(Blueprint $table): void
    {
        $table->string('slug');
        $table->string('title')->nullable();
        $table->longText('description')->nullable();
        $table->string('password')->nullable();
        $table->string('owner_token');
        $table->string('preview_token');
        $table->integer('fullsize')->default(0);
        $table->integer('max_downloads')->nullable();
        $table->integer('downloads')->default(0);
        $table->boolean('completed')->default(false);
        $table->integer('expiry')->default(0);
        $table->datetime('expires_at')->nullable();
        $table->string('preview_link')->nullable();
        $table->string('download_link')->nullable();
        $table->string('deletion_link')->nullable();
        $table->string('user_username')->nullable();
    }

    public function files(): HasMany
    {
        return $this->hasMany(File::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
