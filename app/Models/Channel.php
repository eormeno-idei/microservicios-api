<?php

namespace App\Models;

use App\Enums\ChannelType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Channel extends Model
{
    /**
     * Los atributos que se pueden asignar masivamente.
     */
    protected $fillable = [
        'name',
        'description',
        'type',
        'semantic_context',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     */
    protected $casts = [
        'type' => ChannelType::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relación N:M con Users.
     * Un canal puede tener muchos usuarios, y un usuario puede estar en muchos canales.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_channels')
            ->withPivot('is_approved', 'approved_at', 'approved_by')
            ->withTimestamps();
    }

    /**
     * Relación N:M con Posts.
     * Un canal puede tener muchos posts, y un post puede publicarse en muchos canales.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_channels', 'channel_id', 'post_id');
    }

    /**
     * Relación N:M con Medias.
     * Un canal puede distribuir contenido a través de muchos medios,
     * y un medio puede distribuir contenido de muchos canales.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function medias(): BelongsToMany
    {
        return $this->belongsToMany(Media::class, 'channel_medias', 'channel_id', 'media_id');
    }
}
