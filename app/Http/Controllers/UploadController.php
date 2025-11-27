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
        $sessionId = session()->getId();

        // Generar nombres únicos
        $tempId = (string) Str::uuid();
        $originalFilename = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $storedFilename = $tempId . '.' . $extension;

        // Guardar en storage temporal
        $path = $file->storeAs(
            "temp/{$sessionId}",
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
            'session_id' => $sessionId,
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
        $sessionId = session()->getId();

        // Buscar registro temporal
        $temp = DB::table('temporary_uploads')
            ->where('id', $id)
            ->where('session_id', $sessionId) // Verificar que sea de la misma sesión
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
}
