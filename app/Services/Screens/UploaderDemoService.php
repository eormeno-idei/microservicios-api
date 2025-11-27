<?php

namespace App\Services\Screens;

use App\Services\UI\AbstractUIService;
use App\Services\UI\Components\UIContainer;
use App\Services\UI\Components\UploaderBuilder;
use App\Services\UI\Components\LabelBuilder;
use App\Services\UI\UIBuilder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Uploader Demo Service
 *
 * Demuestra el uso del componente UploaderBuilder
 */
class UploaderDemoService extends AbstractUIService
{
    protected UploaderBuilder $uploader_profile;
    protected UploaderBuilder $uploader_banner;
    protected UploaderBuilder $uploader_story;
    protected UploaderBuilder $uploader_images;
    protected UploaderBuilder $uploader_documents;
    protected LabelBuilder $lbl_result;
    protected $btn_confirm_profile;
    protected $btn_confirm_banner;
    protected $btn_confirm_story;
    protected $btn_confirm_images;
    protected $btn_confirm_documents;

    protected function buildBaseUI(UIContainer $container, ...$params): void
    {
        $container
            ->title('Uploader Component Demo')
            ->maxWidth('800px')
            ->centerHorizontal()
            ->shadow(2)
            ->padding('30px');

        // Instrucciones
        $container->add(
            UIBuilder::label('lbl_instructions')
                ->text('📤 Prueba el componente de upload de archivos')
                ->style('info')
        );

        // Uploader de imagen única (perfil)
        $container->add(
            UIBuilder::label('lbl_profile_title')
                ->text('👤 Imagen de Perfil (1:1 - Size 2)')
                ->style('primary')
        );

        $container->add(
            UIBuilder::uploader('uploader_profile')
                ->allowedTypes(['image/*'])
                ->label('Foto de perfil cuadrada - 192x192px')
                ->maxFiles(1)
                ->maxSize(2)
                ->aspect('1:1')
                ->size(2)
        );

        $container->add(
            UIBuilder::button('btn_confirm_profile')
                ->label('✅ Confirmar Foto de Perfil')
                ->style('success')
                ->action('process_profile')
        );

        // Separador
        $container->add(
            UIBuilder::label('lbl_separator1')
                ->text('───────────────────────────')
                ->style('secondary')
        );

        // Uploader de banner (16:9)
        $container->add(
            UIBuilder::label('lbl_banner_title')
                ->text('🖼️ Banner Horizontal (16:9 - Size 3)')
                ->style('primary')
        );

        $container->add(
            UIBuilder::uploader('uploader_banner')
                ->allowedTypes(['image/*'])
                ->label('Banner para posts - 256x144px')
                ->maxFiles(1)
                ->maxSize(3)
                ->aspect('16:9')
                ->size(3)
        );

        $container->add(
            UIBuilder::button('btn_confirm_banner')
                ->label('✅ Confirmar Banner')
                ->style('success')
                ->action('process_banner')
        );

        // Separador
        $container->add(
            UIBuilder::label('lbl_separator2')
                ->text('───────────────────────────')
                ->style('secondary')
        );

        // Uploader de story (9:16)
        $container->add(
            UIBuilder::label('lbl_story_title')
                ->text('📱 Story Vertical (9:16 - Size 2)')
                ->style('primary')
        );

        $container->add(
            UIBuilder::uploader('uploader_story')
                ->allowedTypes(['image/*'])
                ->label('Imagen para stories - 108x192px')
                ->maxFiles(1)
                ->maxSize(3)
                ->aspect('9:16')
                ->size(2)
        );

        $container->add(
            UIBuilder::button('btn_confirm_story')
                ->label('✅ Confirmar Story')
                ->style('success')
                ->action('process_story')
        );

        // Separador
        $container->add(
            UIBuilder::label('lbl_separator3')
                ->text('───────────────────────────')
                ->style('secondary')
        );

        // Uploader de imágenes
        $container->add(
            UIBuilder::label('lbl_images_title')
                ->text('🖼️ Upload Múltiple de Imágenes (sin aspect ratio)')
                ->style('primary')
        );

        $container->add(
            UIBuilder::uploader('uploader_images')
                ->images()
                ->label('Selecciona múltiples imágenes')
                ->maxFiles(3)
                ->maxSize(5)
        );

        $container->add(
            UIBuilder::button('btn_confirm_images')
                ->label('✅ Confirmar Imágenes')
                ->style('success')
                ->action('process_images')
        );

        // Separador
        $container->add(
            UIBuilder::label('lbl_separator4')
                ->text('───────────────────────────')
                ->style('secondary')
        );

        // Uploader de documentos
        $container->add(
            UIBuilder::label('lbl_documents_title')
                ->text('📄 Upload de Documentos')
                ->style('primary')
        );

        $container->add(
            UIBuilder::uploader('uploader_documents')
                ->documents()
                ->label('Selecciona documentos (PDF, Word, Excel)')
                ->maxFiles(2)
                ->maxSize(10)
        );

        $container->add(
            UIBuilder::button('btn_confirm_documents')
                ->label('✅ Confirmar Documentos')
                ->style('success')
                ->action('process_documents')
        );

        // Resultado
        $container->add(
            UIBuilder::label('lbl_result')
                ->text('Sube archivos para ver el resultado')
                ->style('secondary')
        );
    }

    protected function postLoadUI(): void
    {
        $this->lbl_result
            ->text('Sube archivos para ver el resultado')
            ->style('secondary');
    }

    /**
     * Procesar foto de perfil
     */
    public function onProcessProfile(array $params): void
    {
        // Obtener temp_ids del input hidden del uploader
        $tempIdsJson = $params['uploader_profile_temp_ids'] ?? '[]';
        $tempIds = json_decode($tempIdsJson, true) ?: [];

        if (empty($tempIds)) {
            $this->lbl_result
                ->text('❌ No hay foto de perfil para procesar')
                ->style('danger');
            return;
        }

        // Obtener archivo temporal
        $file = DB::table('temporary_uploads')
            ->where('id', $tempIds[0])
            ->first();

        if (!$file) {
            $this->lbl_result
                ->text('❌ Archivo temporal no encontrado')
                ->style('danger');
            return;
        }

        // Mover archivo de temporal a final
        $finalPath = 'uploads/profiles/' . $file->stored_filename;
        Storage::move($file->path, $finalPath);

        // Limpiar temporal
        DB::table('temporary_uploads')->where('id', $file->id)->delete();

        // Mostrar resultado
        $sizeMB = round($file->size / 1024 / 1024, 2);
        $result = "✅ Foto de perfil procesada exitosamente:\n\n";
        $result .= "📸 {$file->original_filename} ({$sizeMB}MB)\n";
        $result .= "   → {$finalPath}";

        $this->lbl_result
            ->text($result)
            ->style('success');

        // Limpiar uploader después de procesar
        $this->uiChanges()->add([
            'clear_uploaders' => [$this->uploader_profile->getId()]
        ]);
    }

    /**
     * Procesar banner
     */
    public function onProcessBanner(array $params): void
    {
        $tempIdsJson = $params['uploader_banner_temp_ids'] ?? '[]';
        $tempIds = json_decode($tempIdsJson, true) ?: [];

        if (empty($tempIds)) {
            $this->lbl_result
                ->text('❌ No hay banner para procesar')
                ->style('danger');
            return;
        }

        $file = DB::table('temporary_uploads')->where('id', $tempIds[0])->first();

        if (!$file) {
            $this->lbl_result
                ->text('❌ Archivo temporal no encontrado')
                ->style('danger');
            return;
        }

        $finalPath = 'uploads/banners/' . $file->stored_filename;
        Storage::move($file->path, $finalPath);
        DB::table('temporary_uploads')->where('id', $file->id)->delete();

        $sizeMB = round($file->size / 1024 / 1024, 2);
        $result = "✅ Banner procesado exitosamente (16:9):\n\n";
        $result .= "🖼️ {$file->original_filename} ({$sizeMB}MB)\n";
        $result .= "   → {$finalPath}";

        $this->lbl_result
            ->text($result)
            ->style('success');

        $this->uiChanges()->add([
            'clear_uploaders' => [$this->uploader_banner->getId()]
        ]);
    }

    /**
     * Procesar story
     */
    public function onProcessStory(array $params): void
    {
        $tempIdsJson = $params['uploader_story_temp_ids'] ?? '[]';
        $tempIds = json_decode($tempIdsJson, true) ?: [];

        if (empty($tempIds)) {
            $this->lbl_result
                ->text('❌ No hay story para procesar')
                ->style('danger');
            return;
        }

        $file = DB::table('temporary_uploads')->where('id', $tempIds[0])->first();

        if (!$file) {
            $this->lbl_result
                ->text('❌ Archivo temporal no encontrado')
                ->style('danger');
            return;
        }

        $finalPath = 'uploads/stories/' . $file->stored_filename;
        Storage::move($file->path, $finalPath);
        DB::table('temporary_uploads')->where('id', $file->id)->delete();

        $sizeMB = round($file->size / 1024 / 1024, 2);
        $result = "✅ Story procesada exitosamente (9:16):\n\n";
        $result .= "📱 {$file->original_filename} ({$sizeMB}MB)\n";
        $result .= "   → {$finalPath}";

        $this->lbl_result
            ->text($result)
            ->style('success');

        $this->uiChanges()->add([
            'clear_uploaders' => [$this->uploader_story->getId()]
        ]);
    }

    /**
     * Procesar imágenes subidas
     */
    public function onProcessImages(array $params): void
    {
        // Obtener temp_ids del input hidden del uploader
        $tempIdsJson = $params['uploader_images_temp_ids'] ?? '[]';
        $tempIds = json_decode($tempIdsJson, true) ?: [];

        if (empty($tempIds)) {
            $this->lbl_result
                ->text('❌ No hay imágenes para procesar')
                ->style('danger');
            return;
        }

        // Obtener archivos temporales
        $files = DB::table('temporary_uploads')
            ->whereIn('id', $tempIds)
            ->get();

        $processedFiles = [];

        foreach ($files as $temp) {
            // Mover archivo de temporal a final
            $finalPath = 'uploads/images/' . $temp->stored_filename;
            Storage::move($temp->path, $finalPath);

            // Aquí podrías guardar en BD si fuera necesario
            // DB::table('images')->insert([...]);

            $processedFiles[] = [
                'original' => $temp->original_filename,
                'size' => $temp->size,
                'path' => $finalPath,
            ];

            // Limpiar temporal
            DB::table('temporary_uploads')->where('id', $temp->id)->delete();
        }

        // Mostrar resultado
        $result = "✅ Imágenes procesadas exitosamente:\n\n";
        foreach ($processedFiles as $file) {
            $sizeMB = round($file['size'] / 1024 / 1024, 2);
            $result .= "📸 {$file['original']} ({$sizeMB}MB)\n";
            $result .= "   → {$file['path']}\n\n";
        }

        $this->lbl_result
            ->text($result)
            ->style('success');

        // Limpiar uploader después de procesar
        $this->uiChanges()->add([
            'clear_uploaders' => [$this->uploader_images->getId()]
        ]);
    }

    /**
     * Procesar documentos subidos
     */
    public function onProcessDocuments(array $params): void
    {
        // Obtener temp_ids del input hidden del uploader
        $tempIdsJson = $params['uploader_documents_temp_ids'] ?? '[]';
        $tempIds = json_decode($tempIdsJson, true) ?: [];

        if (empty($tempIds)) {
            $this->lbl_result
                ->text('❌ No hay documentos para procesar')
                ->style('danger');
            return;
        }

        // Obtener archivos temporales
        $files = DB::table('temporary_uploads')
            ->whereIn('id', $tempIds)
            ->get();

        $processedFiles = [];

        foreach ($files as $temp) {
            // Mover archivo de temporal a final
            $finalPath = 'uploads/documents/' . $temp->stored_filename;
            Storage::move($temp->path, $finalPath);

            // Aquí podrías guardar en BD si fuera necesario
            // DB::table('documents')->insert([...]);

            $processedFiles[] = [
                'original' => $temp->original_filename,
                'type' => $temp->type,
                'size' => $temp->size,
                'path' => $finalPath,
            ];

            // Limpiar temporal
            DB::table('temporary_uploads')->where('id', $temp->id)->delete();
        }

        // Mostrar resultado
        $result = "✅ Documentos procesados exitosamente:\n\n";
        foreach ($processedFiles as $file) {
            $sizeMB = round($file['size'] / 1024 / 1024, 2);
            $emoji = $this->getDocumentEmoji($file['original']);
            $result .= "{$emoji} {$file['original']} ({$sizeMB}MB)\n";
            $result .= "   → {$file['path']}\n\n";
        }

        $this->lbl_result
            ->text($result)
            ->style('success');

        // Limpiar uploader después de procesar
        $this->uiChanges()->add([
            'clear_uploaders' => [$this->uploader_documents->getId()]
        ]);
    }

    /**
     * Obtener emoji según tipo de documento
     */
    private function getDocumentEmoji(string $filename): string
    {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        return match ($ext) {
            'pdf' => '📄',
            'doc', 'docx' => '📝',
            'xls', 'xlsx' => '📊',
            'ppt', 'pptx' => '📽️',
            default => '📎',
        };
    }
}
