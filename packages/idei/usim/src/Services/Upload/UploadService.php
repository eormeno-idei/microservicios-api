<?php

namespace Idei\Usim\Services\Upload;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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

    /**
     * Persistir archivo temporal a ubicación final
     *
     * Mueve un archivo temporal a su ubicación final, elimina el archivo anterior si existe,
     * y limpia el registro temporal de la base de datos.
     *
     * @param string $tempId UUID del archivo temporal
     * @param string $category Categoría del archivo (ej: 'images', 'documents', 'videos')
     * @param string|null $oldFilename Nombre del archivo anterior a eliminar (solo el nombre, sin ruta)
     * @return string|null Nombre del archivo guardado (solo nombre, sin ruta) o null si falla
     *
     * @example
     * // Mover archivo temporal a uploads/images/ y eliminar imagen anterior
     * $filename = UploadService::persistTemporaryUpload($tempId, 'images', $user->profile_image);
     * if ($filename) {
     *     $user->profile_image = $filename;
     *     $user->save();
     * }
     */
    public static function persistTemporaryUpload(string $tempId, string $category, ?string $oldFilename = null): ?string
    {
        // Obtener archivo temporal
        $file = DB::table('temporary_uploads')
            ->where('id', $tempId)
            ->first();

        if (!$file) {
            return null;
        }

        try {
            // Eliminar archivo anterior si existe
            if ($oldFilename) {
                self::deleteFile($category, $oldFilename);
            }

            // Definir ruta final
            $finalPath = "uploads/{$category}/{$file->stored_filename}";

            // Mover de temporal a definitivo
            $content = Storage::disk('local')->get($file->path);
            Storage::disk('uploads')->put($finalPath, $content);

            // Eliminar temporal del storage
            Storage::disk('local')->delete($file->path);

            // Limpiar registro temporal
            DB::table('temporary_uploads')->where('id', $file->id)->delete();

            return $file->stored_filename;

        } catch (\Exception $e) {
            Log::error('UploadService: Error persistiendo archivo temporal', [
                'temp_id' => $tempId,
                'category' => $category,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Eliminar archivo del storage
     *
     * @param string $category Categoría del archivo (ej: 'images', 'documents')
     * @param string $filename Nombre del archivo (sin ruta)
     * @return bool true si se eliminó o no existía, false si hubo error
     */
    public static function deleteFile(string $category, string $filename): bool
    {
        try {
            $path = "uploads/{$category}/{$filename}";

            if (Storage::disk('uploads')->exists($path)) {
                return Storage::disk('uploads')->delete($path);
            }

            return true; // No existe, consideramos éxito
        } catch (\Exception $e) {
            Log::error('UploadService: Error eliminando archivo', [
                'category' => $category,
                'filename' => $filename,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Procesar múltiples archivos temporales (útil para uploaders con max_files > 1)
     *
     * @param array $tempIds Array de UUIDs de archivos temporales
     * @param string $category Categoría de archivos
     * @return array Array de nombres de archivos guardados
     */
    public static function persistMultipleTemporaryUploads(array $tempIds, string $category): array
    {
        $filenames = [];

        foreach ($tempIds as $tempId) {
            $filename = self::persistTemporaryUpload($tempId, $category);
            if ($filename) {
                $filenames[] = $filename;
            }
        }

        return $filenames;
    }
}
