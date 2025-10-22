<?php

namespace App\Models;

use App\Enums\MediaType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Media extends Model
{
    protected $table = 'medias';

    protected $fillable = [
        'name',
        'type',
        'configuration',
        'semantic_context',
        'url_webhook',
        'is_active',
    ];

    protected $casts = [
        'type' => MediaType::class,
        'configuration' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Un medio puede distribuir muchos posts (relación N:M)
     */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_medias');
    }

    /**
     * Un medio puede estar asociado a muchos canales (relación N:M)
     */
    public function channels(): BelongsToMany
    {
        return $this->belongsToMany(Channel::class, 'channel_medias');
    }

    /**
     * Scope para obtener solo medios activos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para filtrar por tipo
     */
    public function scopeOfType($query, MediaType $type)
    {
        return $query->where('type', $type);
    }
}
