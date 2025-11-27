<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Clean Temporary Uploads Job
 *
 * Elimina archivos temporales que han expirado (> 24 horas)
 * Se ejecuta cada hora vía schedule
 */
class CleanTemporaryUploadsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Starting cleanup of expired temporary uploads');

        // Obtener archivos expirados
        $expired = DB::table('temporary_uploads')
            ->where('expires_at', '<', now())
            ->get();

        $deletedCount = 0;
        $failedCount = 0;

        foreach ($expired as $temp) {
            try {
                // Eliminar archivo del storage
                if (Storage::exists($temp->path)) {
                    Storage::delete($temp->path);
                }

                // Eliminar registro de BD
                DB::table('temporary_uploads')->where('id', $temp->id)->delete();

                $deletedCount++;
            } catch (\Exception $e) {
                $failedCount++;
                Log::error('Failed to delete temporary upload', [
                    'id' => $temp->id,
                    'path' => $temp->path,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Limpiar directorios vacíos en temp/
        $this->cleanEmptyDirectories();

        Log::info('Temporary uploads cleanup completed', [
            'deleted' => $deletedCount,
            'failed' => $failedCount,
        ]);
    }

    /**
     * Limpiar directorios vacíos en storage/app/temp/
     */
    private function cleanEmptyDirectories(): void
    {
        try {
            $tempPath = storage_path('app/temp');

            if (!is_dir($tempPath)) {
                return;
            }

            // Listar directorios de sesión
            $sessionDirs = scandir($tempPath);

            foreach ($sessionDirs as $dir) {
                if ($dir === '.' || $dir === '..') {
                    continue;
                }

                $dirPath = $tempPath . '/' . $dir;

                if (is_dir($dirPath)) {
                    // Verificar si está vacío
                    $files = scandir($dirPath);
                    $isEmpty = count($files) <= 2; // Solo . y ..

                    if ($isEmpty) {
                        rmdir($dirPath);
                        Log::debug("Removed empty directory: {$dir}");
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to clean empty directories', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
