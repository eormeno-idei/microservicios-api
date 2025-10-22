<?php

namespace App\Models;

use App\Models\Channel;
use App\Enums\MediaType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Media extends Model
{
    /**
     * Especifica el nombre de la tabla explícitamente.
     * Esto previene errores cuando Laravel intenta inferir "media" (singular).
     */
    protected $table = 'medias'; // (¡importante!: El plural es "media" y el singular también "medium")

    /**
     * Los atributos que se pueden asignar masivamente.
     */
    protected $fillable = [
        'name',
        'type',
        'configuration',
        'semantic_context',
        'url_webhook',
        'is_active',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     */
    protected $casts = [
        'type' => MediaType::class,
        'configuration' => 'array',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relación N:M con Posts.
     * Un medio puede tener muchos posts, y un post puede tener muchos medios.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_medias', 'media_id', 'post_id');
    }

    /**
     * Relación N:M con Channels.
     * Un medio puede distribuir contenido a muchos canales,
     * y un canal puede usar muchos medios.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function channels(): BelongsToMany
    {
        return $this->belongsToMany(Channel::class, 'channel_medias', 'media_id', 'channel_id');
    }
}
