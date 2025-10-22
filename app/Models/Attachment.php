<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attachment extends Model
{
    // No tiene timestamps según la migración
    public $timestamps = false;

    protected $fillable = [
        'post_id',
        'mime_type',
        'path',
    ];

    /**
     * Un attachment pertenece a un post (relación 1:N inversa)
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Obtener el tipo de archivo basado en mime_type
     */
    public function getFileTypeAttribute(): string
    {
        $mimeType = $this->mime_type;

        if (str_starts_with($mimeType, 'image/')) {
            return 'image';
        } elseif (str_starts_with($mimeType, 'video/')) {
            return 'video';
        } elseif (str_starts_with($mimeType, 'audio/')) {
            return 'audio';
        } elseif (str_contains($mimeType, 'pdf')) {
            return 'document';
        }

        return 'other';
    }

    /**
     * Obtener la URL pública del attachment
     */
    public function getUrlAttribute(): string
    {
        return url($this->path);
    }
}
