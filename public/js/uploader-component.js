/**
 * Uploader Component
 *
 * Componente para upload de archivos con:
 * - Drag & drop
 * - Preview según tipo (imagen, audio, video, documento)
 * - Upload inmediato a storage temporal
 * - Validación de tipo y tamaño
 * - Emojis para iconos
 */
class UploaderComponent extends UIComponent {
    constructor(id, config) {
        super(id, config);
        this.uploadedFiles = []; // Array de archivos subidos {temp_id, filename, size, type, ...}
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

        // Calcular dimensiones del dropzone según aspect_ratio y size_level
        this.dropzoneDimensions = this.calculateDropzoneDimensions();
    }

    render() {
        const container = document.createElement('div');
        container.className = 'ui-uploader-group';
        this.applyCommonAttributes(container);

        // Detectar si es modo imagen única
        this.isSingleImageMode = this.config.max_files === 1 &&
                                 this.config.allowed_types &&
                                 this.config.allowed_types.length === 1 &&
                                 this.config.allowed_types[0] === 'image/*';

        // Label
        if (this.config.label) {
            const label = document.createElement('label');
            label.className = 'ui-uploader-label';
            label.textContent = this.config.label;
            container.appendChild(label);
        }

        // Dropzone (contendrá la lista de archivos dentro)
        const dropzone = this.createDropZone();
        if (this.isSingleImageMode) {
            dropzone.classList.add('ui-uploader-single-image-mode');
        }
        container.appendChild(dropzone);

        // Input hidden para almacenar temp_ids
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = this.config.name ? `${this.config.name}_temp_ids` : `uploader_${this.id}_temp_ids`;
        hiddenInput.setAttribute('data-uploader-input', this.id);
        hiddenInput.value = '[]';
        container.appendChild(hiddenInput);

        return container;
    }

    createDropZone() {
        const dropzone = document.createElement('div');
        dropzone.className = 'ui-uploader-dropzone';

        // Aplicar dimensiones personalizadas si están definidas
        if (this.dropzoneDimensions.width) {
            dropzone.style.width = `${this.dropzoneDimensions.width}px`;
        }
        if (this.dropzoneDimensions.height) {
            dropzone.style.minHeight = `${this.dropzoneDimensions.height}px`;
            dropzone.style.height = `${this.dropzoneDimensions.height}px`;
        }

        // Contenido del dropzone (mensaje inicial)
        const dropzoneContent = document.createElement('div');
        dropzoneContent.className = 'ui-uploader-dropzone-content';

        // Si tiene aspect ratio, mostrar solo icono animado
        if (this.config.aspect_ratio) {
            dropzoneContent.innerHTML = `
                <span class="ui-uploader-icon">📁</span>
                <p class="ui-uploader-hint-hover">
                    Máximo ${this.config.max_files} archivo(s) · ${this.config.max_size}MB
                </p>
            `;
        } else {
            // Modo normal con texto completo
            dropzoneContent.innerHTML = `
                <span class="ui-uploader-icon">📁</span>
                <p class="ui-uploader-text">Arrastra archivos aquí o haz clic para seleccionar</p>
                <p class="ui-uploader-hint">
                    Máximo ${this.config.max_files} archivo(s) · Tamaño máximo: ${this.config.max_size}MB
                </p>
            `;
        }
        dropzone.appendChild(dropzoneContent);

        // File list (dentro del dropzone)
        const fileList = document.createElement('div');
        fileList.className = 'ui-uploader-file-list';
        fileList.setAttribute('data-uploader-list', this.id);
        dropzone.appendChild(fileList);

        // Input file oculto
        const fileInput = document.createElement('input');
        fileInput.type = 'file';
        fileInput.className = 'ui-uploader-input';
        fileInput.accept = this.config.accept || '*/*';
        fileInput.multiple = this.config.multiple !== false;
        fileInput.style.display = 'none';
        dropzone.appendChild(fileInput);

        // Click en dropzone abre selector
        dropzone.addEventListener('click', (e) => {
            if (e.target !== fileInput) {
                fileInput.click();
            }
        });

        // Eventos de drag & drop
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, this.preventDefaults, false);
        });

        ['dragenter', 'dragover'].forEach(eventName => {
            dropzone.addEventListener(eventName, () => {
                dropzone.classList.add('ui-uploader-dragover');
            });
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, () => {
                dropzone.classList.remove('ui-uploader-dragover');
            });
        });

        dropzone.addEventListener('drop', (e) => {
            const files = e.dataTransfer.files;
            this.handleFiles(files);
        });

        fileInput.addEventListener('change', (e) => {
            this.handleFiles(e.target.files);
            // Reset input para permitir seleccionar el mismo archivo de nuevo
            e.target.value = '';
        });

        return dropzone;
    }

    preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    handleFiles(files) {
        const filesArray = Array.from(files);

        // Validar cantidad máxima
        const remaining = this.config.max_files - this.uploadedFiles.length;
        if (filesArray.length > remaining) {
            this.showError(`Solo puedes subir ${remaining} archivo(s) más`);
            return;
        }

        // Procesar cada archivo
        filesArray.forEach(file => {
            const error = this.validateFile(file);
            if (error) {
                this.showError(`${file.name}: ${error}`);
            } else {
                // Si es modo single image con aspect ratio, verificar y abrir crop editor si es necesario
                if (this.isSingleImageMode && this.config.aspect_ratio && file.type.startsWith('image/')) {
                    this.checkAndCropImage(file);
                } else {
                    this.uploadFile(file);
                }
            }
        });
    }

    async checkAndCropImage(file) {
        // Cargar imagen para verificar aspect ratio
        const img = await this.loadImageFromFile(file);

        const imageAspect = img.width / img.height;
        const [targetW, targetH] = this.config.aspect_ratio.split(':').map(Number);
        const targetAspect = targetW / targetH;

        // Tolerancia del 1% para evitar crop innecesario por diferencias mínimas
        const tolerance = 0.01;
        const aspectDiff = Math.abs(imageAspect - targetAspect);

        if (aspectDiff > tolerance) {
            // Aspect ratio incorrecto, abrir crop editor
            console.log(`📐 Aspect ratio incorrecto: ${imageAspect.toFixed(2)} vs ${targetAspect.toFixed(2)}`);

            if (typeof ImageCropEditor === 'undefined') {
                console.error('ImageCropEditor no está disponible');
                this.showError('Editor de recorte no disponible');
                return;
            }

            new ImageCropEditor(file, this.config.aspect_ratio, (croppedFile) => {
                // Callback con archivo recortado
                this.uploadFile(croppedFile);
            });
        } else {
            // Aspect ratio correcto, subir directamente
            console.log(`✅ Aspect ratio correcto: ${imageAspect.toFixed(2)}`);
            this.uploadFile(file);
        }
    }

    loadImageFromFile(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = (e) => {
                const img = new Image();
                img.onload = () => resolve(img);
                img.onerror = reject;
                img.src = e.target.result;
            };
            reader.onerror = reject;
            reader.readAsDataURL(file);
        });
    }

    validateFile(file) {
        // Validar tamaño
        const sizeMB = file.size / 1024 / 1024;
        if (sizeMB > this.config.max_size) {
            return `Archivo muy grande (máx. ${this.config.max_size}MB)`;
        }

        // Validar tipo
        const allowedTypes = this.config.allowed_types || ['*'];
        if (!allowedTypes.includes('*') && !this.matchesMimePattern(file.type, allowedTypes)) {
            return 'Tipo de archivo no permitido';
        }

        return null;
    }

    matchesMimePattern(mimeType, patterns) {
        return patterns.some(pattern => {
            if (pattern === mimeType) return true;
            if (pattern.endsWith('/*')) {
                const prefix = pattern.replace('/*', '');
                return mimeType.startsWith(prefix + '/');
            }
            return false;
        });
    }

    async uploadFile(file) {
        const formData = new FormData();
        formData.append('file', file);
        formData.append('component_id', this.id);
        formData.append('_token', this.csrfToken);

        // Crear item en la lista (con estado "uploading")
        const fileItem = this.createFileItem(file, 'uploading');
        const fileList = document.querySelector(`[data-uploader-list="${this.id}"]`);
        fileList.appendChild(fileItem);

        // Ocultar contenido del dropzone si es modo imagen única
        if (this.isSingleImageMode) {
            const dropzoneContent = document.querySelector(`[data-component-id="${this.id}"] .ui-uploader-dropzone-content`);
            if (dropzoneContent) {
                dropzoneContent.style.display = 'none';
            }
        }

        try {
            const response = await fetch('/api/upload/temporary', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                },
                body: formData,
            });

            const result = await response.json();

            if (result.success) {
                // Actualizar item con datos del servidor
                this.updateFileItem(fileItem, result.data, 'success');

                // Agregar a lista de archivos subidos
                this.uploadedFiles.push(result.data);
                this.updateHiddenInput();
            } else {
                this.updateFileItem(fileItem, { original_filename: file.name }, 'error');
                this.showError(result.message || 'Error al subir archivo');
            }
        } catch (error) {
            console.error('Upload error:', error);
            this.updateFileItem(fileItem, { original_filename: file.name }, 'error');
            this.showError('Error de conexión');
        }
    }

    createFileItem(file, status = 'uploading') {
        const item = document.createElement('div');
        item.className = `ui-uploader-file-item ui-uploader-file-${status}`;

        // En modo imagen única, usar clase especial
        if (this.isSingleImageMode) {
            item.classList.add('ui-uploader-single-image');
        }

        const preview = this.createPreview(file);
        item.appendChild(preview);

        // En modo normal, mostrar info del archivo
        if (!this.isSingleImageMode) {
            const info = document.createElement('div');
            info.className = 'ui-uploader-file-info';
            info.innerHTML = `
                <span class="ui-uploader-filename">${file.name}</span>
                <span class="ui-uploader-filesize">${this.formatFileSize(file.size)}</span>
            `;
            item.appendChild(info);
        }

        if (status === 'uploading') {
            const spinner = document.createElement('div');
            spinner.className = 'ui-uploader-spinner';
            spinner.textContent = '⏳';
            item.appendChild(spinner);
        }

        return item;
    }

    updateFileItem(item, fileData, status) {
        item.className = `ui-uploader-file-item ui-uploader-file-${status}`;

        // Mantener la clase single image si aplica
        if (this.isSingleImageMode) {
            item.classList.add('ui-uploader-single-image');
        }

        // Remover spinner
        const spinner = item.querySelector('.ui-uploader-spinner');
        if (spinner) spinner.remove();

        // Agregar botón eliminar si es exitoso
        if (status === 'success') {
            item.setAttribute('data-temp-id', fileData.temp_id);

            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'ui-uploader-remove';
            removeBtn.innerHTML = '×';
            removeBtn.title = 'Eliminar archivo';
            removeBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.removeFile(fileData.temp_id, item);
            });
            item.appendChild(removeBtn);
        }
    }

    createPreview(file) {
        const preview = document.createElement('div');
        preview.className = 'ui-uploader-preview';

        const type = this.detectType(file.type);

        switch (type) {
            case 'image':
                const img = document.createElement('img');
                img.src = URL.createObjectURL(file);
                img.onload = () => URL.revokeObjectURL(img.src);
                preview.appendChild(img);
                break;

            case 'audio':
                preview.innerHTML = `
                    <span class="preview-emoji">🎵</span>
                    <span class="duration">...</span>
                `;
                this.extractAudioDuration(file, preview);
                break;

            case 'video':
                preview.innerHTML = `
                    <span class="preview-emoji">🎬</span>
                    <span class="duration">...</span>
                `;
                this.extractVideoDuration(file, preview);
                break;

            case 'pdf':
                preview.innerHTML = '<span class="preview-emoji">📄</span>';
                break;

            default:
                const emoji = this.getDocumentEmoji(file.name);
                preview.innerHTML = `<span class="preview-emoji">${emoji}</span>`;
        }

        return preview;
    }

    detectType(mimeType) {
        if (mimeType.startsWith('image/')) return 'image';
        if (mimeType.startsWith('audio/')) return 'audio';
        if (mimeType.startsWith('video/')) return 'video';
        if (mimeType === 'application/pdf') return 'pdf';
        return 'document';
    }

    getDocumentEmoji(filename) {
        const ext = filename.split('.').pop().toLowerCase();
        const emojiMap = {
            'doc': '📝', 'docx': '📝',
            'xls': '📊', 'xlsx': '📊',
            'ppt': '📽️', 'pptx': '📽️',
            'txt': '📃',
            'zip': '🗜️', 'rar': '🗜️'
        };
        return emojiMap[ext] || '📎';
    }

    extractAudioDuration(file, previewElement) {
        const audio = new Audio(URL.createObjectURL(file));
        audio.onloadedmetadata = () => {
            const duration = this.formatDuration(audio.duration);
            const durationSpan = previewElement.querySelector('.duration');
            if (durationSpan) durationSpan.textContent = duration;
            URL.revokeObjectURL(audio.src);
        };
    }

    extractVideoDuration(file, previewElement) {
        const video = document.createElement('video');
        video.src = URL.createObjectURL(file);
        video.onloadedmetadata = () => {
            const duration = this.formatDuration(video.duration);
            const durationSpan = previewElement.querySelector('.duration');
            if (durationSpan) durationSpan.textContent = duration;
            URL.revokeObjectURL(video.src);
        };
    }

    formatDuration(seconds) {
        const mins = Math.floor(seconds / 60);
        const secs = Math.floor(seconds % 60);
        return `${mins}:${secs.toString().padStart(2, '0')}`;
    }

    formatFileSize(bytes) {
        const units = ['B', 'KB', 'MB', 'GB'];
        const power = bytes > 0 ? Math.floor(Math.log(bytes) / Math.log(1024)) : 0;
        return Math.round(bytes / Math.pow(1024, power) * 100) / 100 + ' ' + units[power];
    }

    async removeFile(tempId, itemElement) {
        try {
            const response = await fetch(`/api/upload/temporary/${tempId}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                },
            });

            const result = await response.json();

            if (result.success) {
                // Remover de la lista visual
                itemElement.remove();

                // Remover de array
                this.uploadedFiles = this.uploadedFiles.filter(f => f.temp_id !== tempId);
                this.updateHiddenInput();

                // Mostrar contenido del dropzone si es modo imagen única y no quedan archivos
                if (this.isSingleImageMode && this.uploadedFiles.length === 0) {
                    const dropzoneContent = document.querySelector(`[data-component-id="${this.id}"] .ui-uploader-dropzone-content`);
                    if (dropzoneContent) {
                        dropzoneContent.style.display = '';
                    }
                }
            } else {
                this.showError('Error al eliminar archivo');
            }
        } catch (error) {
            console.error('Delete error:', error);
            this.showError('Error de conexión');
        }
    }

    updateHiddenInput() {
        const input = document.querySelector(`[data-uploader-input="${this.id}"]`);
        if (input) {
            const tempIds = this.uploadedFiles.map(f => f.temp_id);
            input.value = JSON.stringify(tempIds);
        }
    }

    triggerAction() {
        if (!this.config.action) return;

        // Preparar datos para enviar al Service
        const data = {
            uploaded_files: this.uploadedFiles,
            temp_ids: this.uploadedFiles.map(f => f.temp_id),
        };

        // Usar el sistema de eventos existente de UIRenderer
        if (window.UIRenderer) {
            window.UIRenderer.handleAction(this.config.action, data);
        }
    }

    showError(message) {
        // Crear notificación de error temporal
        const error = document.createElement('div');
        error.className = 'ui-uploader-error';
        error.textContent = message;

        const container = this.element || document.querySelector(`[data-component-id="${this.id}"]`);
        if (container) {
            container.appendChild(error);
            setTimeout(() => error.remove(), 5000);
        } else {
            console.error('Uploader error:', message);
        }
    }

    clearFiles() {
        // Limpiar array de archivos
        this.uploadedFiles = [];

        // Actualizar input hidden
        this.updateHiddenInput();

        // Limpiar lista visual
        const fileList = document.querySelector(`[data-uploader-list="${this.id}"]`);
        if (fileList) {
            fileList.innerHTML = '';
        }

        console.log(`🗑️ Uploader ${this.id} cleared`);
    }

    /**
     * Calcular dimensiones del dropzone según aspect_ratio y size_level
     */
    calculateDropzoneDimensions() {
        const sizeLevel = this.config.size_level || 2;
        const aspectRatio = this.config.aspect_ratio;

        // Dimensiones base según size_level
        const baseSizes = {
            1: 128,
            2: 192,
            3: 256,
            4: 320
        };

        const baseSize = baseSizes[sizeLevel] || baseSizes[2];

        // Si no hay aspect ratio definido, usar dimensiones por defecto
        if (!aspectRatio) {
            return { width: null, height: null };
        }

        // Parsear aspect ratio (ej: "16:9", "1:1", "9:16")
        const [widthRatio, heightRatio] = aspectRatio.split(':').map(Number);

        if (!widthRatio || !heightRatio) {
            return { width: null, height: null };
        }

        // Calcular dimensiones manteniendo el aspect ratio
        let width, height;

        if (widthRatio >= heightRatio) {
            // Landscape o cuadrado
            width = baseSize;
            height = Math.round(baseSize * (heightRatio / widthRatio));
        } else {
            // Portrait
            height = baseSize;
            width = Math.round(baseSize * (widthRatio / heightRatio));
        }

        return { width, height };
    }
}

// Hacer la clase disponible globalmente para ComponentFactory
window.UploaderComponent = UploaderComponent;
