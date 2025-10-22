<?php

namespace App\Models;

use App\Enums\ChannelType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Channel extends Model
{
    protected $fillable = [
        'name',
        'description',
        'type',
        'semantic_context',
    ];

    protected $casts = [
        'type' => ChannelType::class,
    ];

    /**
     * Un canal puede tener muchos posts (relación N:M)
     */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_channels');
    }

    /**
     * Un canal puede tener muchos usuarios (relación N:M)
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_channels');
    }

    /**
     * Un canal puede usar muchos medios (relación N:M)
     */
    public function medias(): BelongsToMany
    {
        return $this->belongsToMany(Media::class, 'channel_medias');
    }
}
