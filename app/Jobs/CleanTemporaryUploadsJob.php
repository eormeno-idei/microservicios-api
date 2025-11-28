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

        Log::info('Temporary uploads cleanup completed', [
            'deleted' => $deletedCount,
            'failed' => $failedCount,
        ]);
    }
}
