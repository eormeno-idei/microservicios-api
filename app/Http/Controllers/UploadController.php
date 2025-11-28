<?php

namespace App\Http\Controllers;

use App\Services\Upload\UploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Upload Controller
 *
 * Maneja uploads temporales de archivos
 */
class UploadController extends Controller
{
    /**
     * Upload archivo a storage temporal
     *
     * POST /api/upload/temporary
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadTemporary(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file',
            'component_id' => 'required|string',
        ]);

        $file = $request->file('file');
        $componentId = $request->input('component_id');
        $userId = auth()->id();

        // Generar nombres únicos
        $tempId = (string) Str::uuid();
        $originalFilename = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $storedFilename = $tempId . '.' . $extension;

        // Guardar en storage temporal
        $path = $file->storeAs(
            "temp/{$userId}",
            $storedFilename,
            'local'
        );

        // Detectar tipo y extraer metadata
        $mimeType = $file->getMimeType();
        $type = UploadService::detectFileType($mimeType);
        $metadata = UploadService::extractMetadata($file);

        // Guardar registro en BD
        DB::table('temporary_uploads')->insert([
            'id' => $tempId,
            'session_id' => session()->getId(), // Mantener por compatibilidad
            'user_id' => $userId,
            'component_id' => $componentId,
            'original_filename' => $originalFilename,
            'stored_filename' => $storedFilename,
            'path' => $path,
            'mime_type' => $mimeType,
            'size' => $file->getSize(),
            'type' => $type,
            'metadata' => json_encode($metadata),
            'expires_at' => now()->addHours(24),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Retornar info del archivo
        return response()->json([
            'success' => true,
            'data' => [
                'temp_id' => $tempId,
                'original_filename' => $originalFilename,
                'stored_filename' => $storedFilename,
                'size' => $file->getSize(),
                'size_formatted' => UploadService::formatFileSize($file->getSize()),
                'mime_type' => $mimeType,
                'type' => $type,
                'metadata' => $metadata,
            ],
        ]);
    }

    /**
     * Eliminar archivo temporal
     *
     * DELETE /api/upload/temporary/{id}
     *
     * @param string $id UUID del temporary_upload
     * @return JsonResponse
     */
    public function deleteTemporary(string $id): JsonResponse
    {
        $userId = auth()->id();

        // Buscar registro temporal
        $temp = DB::table('temporary_uploads')
            ->where('id', $id)
            ->where('user_id', $userId) // Verificar que sea del mismo usuario
            ->first();

        if (!$temp) {
            return response()->json([
                'success' => false,
                'message' => 'File not found or access denied',
            ], 404);
        }

        // Eliminar archivo del storage
        Storage::delete($temp->path);

        // Eliminar registro de BD
        DB::table('temporary_uploads')->where('id', $id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'File deleted successfully',
        ]);
    }

    /**
     * Servir archivo almacenado
     *
     * GET /storage/{path}
     *
     * @param string $path Ruta del archivo
     * @return \Illuminate\Http\Response
     */
    public function serveFile(string $path)
    {
        // Limpiar y validar path
        $path = ltrim($path, '/');

        // Verificar que el archivo existe en el disco 'uploads'
        if (!Storage::disk('uploads')->exists($path)) {
            abort(404, 'File not found');
        }

        // Obtener el archivo
        $file = Storage::disk('uploads')->get($path);

        // Detectar MIME type manualmente
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'pdf' => 'application/pdf',
            'mp3' => 'audio/mpeg',
            'mp4' => 'video/mp4',
            'webm' => 'video/webm',
        ];
        $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';

        // Retornar con el tipo MIME correcto
        return response($file, 200)->header('Content-Type', $mimeType);
    }
}
