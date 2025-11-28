<?php

namespace App\Services\Upload;

use Illuminate\Http\UploadedFile;

/**
 * Upload Service
 *
 * Helpers para manejo de uploads:
 * - Detectar tipo de archivo
 * - Validar archivos
 * - Extraer metadata
 */
class UploadService
{
    /**
     * Detectar tipo de archivo por MIME type
     *
     * @param string $mimeType
     * @return string 'image', 'audio', 'video', 'document', 'other'
     */
    public static function detectFileType(string $mimeType): string
    {
        if (str_starts_with($mimeType, 'image/')) {
            return 'image';
        }

        if (str_starts_with($mimeType, 'audio/')) {
            return 'audio';
        }

        if (str_starts_with($mimeType, 'video/')) {
            return 'video';
        }

        $documentMimes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'text/plain',
        ];

        if (in_array($mimeType, $documentMimes)) {
            return 'document';
        }

        return 'other';
    }

    /**
     * Validar archivo según configuración
     *
     * @param UploadedFile $file
     * @param array $config ['allowed_types' => [...], 'max_size' => int]
     * @return array|null ['error' => 'mensaje'] si hay error, null si es válido
     */
    public static function validateFile(UploadedFile $file, array $config): ?array
    {
        $mimeType = $file->getMimeType();
        $sizeMB = $file->getSize() / 1024 / 1024;

        // Validar tipo
        $allowedTypes = $config['allowed_types'] ?? ['*'];
        if (!in_array('*', $allowedTypes) && !self::matchesMimePattern($mimeType, $allowedTypes)) {
            return ['error' => 'File type not allowed'];
        }

        // Validar tamaño
        $maxSize = $config['max_size'] ?? 10;
        if ($sizeMB > $maxSize) {
            return ['error' => "File too large (max {$maxSize}MB)"];
        }

        return null;
    }

    /**
     * Verificar si MIME type coincide con patrones permitidos
     *
     * @param string $mimeType Ej: 'image/jpeg'
     * @param array $patterns Ej: ['image/*', 'application/pdf']
     * @return bool
     */
    private static function matchesMimePattern(string $mimeType, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            // Exact match
            if ($pattern === $mimeType) {
                return true;
            }

            // Wildcard match (ej: image/*)
            if (str_ends_with($pattern, '/*')) {
                $prefix = str_replace('/*', '', $pattern);
                if (str_starts_with($mimeType, $prefix . '/')) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Extraer metadata según tipo de archivo
     *
     * @param UploadedFile $file
     * @return array
     */
    public static function extractMetadata(UploadedFile $file): array
    {
        $type = self::detectFileType($file->getMimeType());

        switch ($type) {
            case 'image':
                return self::extractImageMetadata($file);

            case 'video':
            case 'audio':
                // Para extraer duración necesitaríamos FFmpeg o getID3
                // Por ahora retornamos metadata básica
                return [];

            default:
                return [];
        }
    }

    /**
     * Extraer metadata de imagen (dimensiones)
     *
     * @param UploadedFile $file
     * @return array ['width' => int, 'height' => int]
     */
    private static function extractImageMetadata(UploadedFile $file): array
    {
        try {
            $imageSize = getimagesize($file->getRealPath());

            if ($imageSize !== false) {
                return [
                    'width' => $imageSize[0],
                    'height' => $imageSize[1],
                ];
            }
        } catch (\Exception $e) {
            // Ignorar errores
        }

        return [];
    }

    /**
     * Formatear tamaño de archivo para mostrar
     *
     * @param int $bytes
     * @return string Ej: "2.5 MB"
     */
    public static function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $power = $bytes > 0 ? floor(log($bytes, 1024)) : 0;

        return round($bytes / pow(1024, $power), 2) . ' ' . $units[$power];
    }

    /**
     * Generar URL para acceder a archivo almacenado
     *
     * @param string $path Ruta relativa en storage (ej: 'uploads/profiles/abc.jpg')
     * @return string URL completa (ej: 'http://localhost/files/uploads/profiles/abc.jpg')
     */
    public static function fileUrl(string $path): string
    {
        return url('/files/' . ltrim($path, '/'));
    }
}
