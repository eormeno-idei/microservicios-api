<?php

namespace App\Services\UI\Components;

/**
 * Uploader Component Builder
 *
 * Maneja upload de archivos con:
 * - Upload inmediato a storage temporal
 * - Preview según tipo de archivo (imagen, audio, video, documentos)
 * - Los archivos temporales se procesan en el Service via actions
 */
class UploaderBuilder extends UIComponent
{
    /**
     * Constructor
     */
    public function __construct(?string $name = null)
    {
        parent::__construct($name);
    }

    /**
     * Configuración por defecto
     */
    protected function getDefaultConfig(): array
    {
        return [
            'label' => 'Upload Files',
            'allowed_types' => ['*'], // ['image/*', 'application/pdf', etc.]
            'max_size' => 10, // MB
            'max_files' => 5,
            'multiple' => true,
            'accept' => '*/*', // HTML accept attribute
            'action' => null, // Action name para procesar en Service
            'aspect_ratio' => null, // '1:1', '16:9', '9:16', '4:3', etc.
            'size_level' => 2, // 1-4 (1=128px, 2=192px, 3=256px, 4=320px base)
            'existing_file' => null, // URL de archivo existente para mostrar en preview
        ];
    }

    /**
     * Establecer etiqueta
     */
    public function label(string $label): self
    {
        return $this->setConfig('label', $label);
    }

    /**
     * Tipos de archivo permitidos
     *
     * @param array $types Ej: ['image/*', 'application/pdf', 'video/mp4']
     */
    public function allowedTypes(array $types): self
    {
        $this->setConfig('allowed_types', $types);
        $this->setConfig('accept', implode(',', $types));
        return $this;
    }

    /**
     * Tamaño máximo por archivo en MB
     */
    public function maxSize(int $mb): self
    {
        return $this->setConfig('max_size', $mb);
    }

    /**
     * Cantidad máxima de archivos
     */
    public function maxFiles(int $count): self
    {
        return $this->setConfig('max_files', $count);
    }

    /**
     * Permitir upload múltiple
     */
    public function multiple(bool $multiple = true): self
    {
        return $this->setConfig('multiple', $multiple);
    }

    /**
     * Establecer action para procesar uploads
     * El Service debe implementar el método on{Action}(array $params)
     *
     * @param string $action Nombre de la acción (ej: 'process_uploads')
     */
    public function action(string $action): self
    {
        return $this->setConfig('action', $action);
    }

    /**
     * Establecer relación de aspecto esperada para el dropzone
     *
     * @param string $ratio Formato "width:height" (ej: '1:1', '16:9', '9:16', '4:3')
     */
    public function aspect(string $ratio): self
    {
        return $this->setConfig('aspect_ratio', $ratio);
    }

    /**
     * Establecer nivel de tamaño del dropzone (1-4)
     *
     * 1 = 128px base
     * 2 = 192px base (default)
     * 3 = 256px base
     * 4 = 320px base
     *
     * @param int $level Nivel de tamaño (1-4)
     */
    public function size(int $level): self
    {
        $level = max(1, min(4, $level)); // Clamp entre 1-4
        return $this->setConfig('size_level', $level);
    }

    // ========== SHORTCUTS ==========

    /**
     * Shortcut: Solo imágenes
     */
    public function images(): self
    {
        return $this
            ->allowedTypes(['image/*'])
            ->maxSize(5)
            ->maxFiles(10);
    }

    /**
     * Shortcut: Solo documentos (PDF, Word, Excel)
     */
    public function documents(): self
    {
        return $this
            ->allowedTypes([
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])
            ->maxSize(20)
            ->maxFiles(5);
    }

    /**
     * Shortcut: Multimedia (imágenes, audio, video)
     */
    public function media(): self
    {
        return $this
            ->allowedTypes(['image/*', 'audio/*', 'video/*'])
            ->maxSize(50)
            ->maxFiles(10);
    }

    /**
     * Shortcut: Solo audio
     */
    public function audio(): self
    {
        return $this
            ->allowedTypes(['audio/*'])
            ->maxSize(30)
            ->maxFiles(5);
    }

    /**
     * Shortcut: Solo video
     */
    public function video(): self
    {
        return $this
            ->allowedTypes(['video/*'])
            ->maxSize(100)
            ->maxFiles(3);
    }

    /**
     * Establecer archivo existente para mostrar en preview
     *
     * @param string|null $url URL del archivo existente
     */
    public function existingFile(?string $url): self
    {
        return $this->setConfig('existing_file', $url);
    }

    /**
     * Obtener los IDs temporales de archivos subidos
     *
     * @return array
     */
    public function getTempIds(): array
    {
        // El input hidden tiene el formato: uploader_{id}_temp_ids
        $inputName = "{$this->name}_temp_ids";

        // Buscar en los datos del request
        $tempIdsJson = request()->input($inputName);

        if (empty($tempIdsJson)) {
            return [];
        }

        // Decodificar JSON
        $tempIds = json_decode($tempIdsJson, true);

        return is_array($tempIds) ? $tempIds : [];
    }
}
