/**
 * Image Crop Editor
 *
 * Editor interactivo de recorte de imágenes con:
 * - Zoom con slider o rueda del mouse
 * - Drag para reposicionar
 * - Preview en tiempo real
 * - Export a canvas con aspect ratio exacto
 */
class ImageCropEditor {
    constructor(imageFile, targetAspectRatio, callback) {
        this.imageFile = imageFile;
        this.targetAspectRatio = targetAspectRatio; // "16:9", "1:1", etc.
        this.callback = callback; // Función a llamar con el blob recortado

        // Parse aspect ratio
        const [w, h] = targetAspectRatio.split(':').map(Number);
        this.aspectWidth = w;
        this.aspectHeight = h;
        this.aspectRatio = w / h;

        // Estado del editor
        this.scale = 1;
        this.minScale = 1;
        this.maxScale = 3;
        this.offsetX = 0;
        this.offsetY = 0;
        this.isDragging = false;
        this.dragStartX = 0;
        this.dragStartY = 0;

        // Elementos DOM
        this.modal = null;
        this.canvas = null;
        this.ctx = null;
        this.image = null;

        this.init();
    }

    async init() {
        // Cargar imagen
        await this.loadImage();

        // Crear modal
        this.createModal();

        // Calcular escala inicial para cubrir el área
        this.calculateInitialScale();

        // Centrar imagen
        this.centerImage();

        // Renderizar
        this.render();

        // Setup eventos
        this.setupEvents();
    }

    loadImage() {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = (e) => {
                const img = new Image();
                img.onload = () => {
                    this.image = img;
                    resolve();
                };
                img.onerror = reject;
                img.src = e.target.result;
            };
            reader.onerror = reject;
            reader.readAsDataURL(this.imageFile);
        });
    }

    createModal() {
        // Overlay
        const overlay = document.createElement('div');
        overlay.className = 'crop-editor-overlay';

        // Modal
        this.modal = document.createElement('div');
        this.modal.className = 'crop-editor-modal';

        // Header
        const header = document.createElement('div');
        header.className = 'crop-editor-header';
        header.innerHTML = `
            <h3>✂️ Ajustar imagen (${this.targetAspectRatio})</h3>
            <p>Arrastra para posicionar · Usa el slider o la rueda para hacer zoom</p>
        `;
        this.modal.appendChild(header);

        // Canvas container
        const canvasContainer = document.createElement('div');
        canvasContainer.className = 'crop-editor-canvas-container';

        // Canvas (tamaño fijo para preview)
        this.canvas = document.createElement('canvas');
        this.canvas.className = 'crop-editor-canvas';

        // Definir tamaño del canvas según aspect ratio
        const maxSize = 400;
        if (this.aspectRatio >= 1) {
            // Landscape o cuadrado
            this.canvas.width = maxSize;
            this.canvas.height = maxSize / this.aspectRatio;
        } else {
            // Portrait
            this.canvas.height = maxSize;
            this.canvas.width = maxSize * this.aspectRatio;
        }

        this.ctx = this.canvas.getContext('2d');
        canvasContainer.appendChild(this.canvas);
        this.modal.appendChild(canvasContainer);

        // Controls
        const controls = document.createElement('div');
        controls.className = 'crop-editor-controls';

        // Zoom slider
        const zoomControl = document.createElement('div');
        zoomControl.className = 'crop-editor-zoom-control';
        zoomControl.innerHTML = `
            <label>🔍 Zoom</label>
            <input type="range" min="100" max="300" value="100" step="1" class="crop-editor-zoom-slider">
            <span class="crop-editor-zoom-value">100%</span>
        `;
        controls.appendChild(zoomControl);

        this.modal.appendChild(controls);

        // Actions
        const actions = document.createElement('div');
        actions.className = 'crop-editor-actions';
        actions.innerHTML = `
            <button type="button" class="crop-editor-btn crop-editor-btn-cancel">Cancelar</button>
            <button type="button" class="crop-editor-btn crop-editor-btn-accept">✂️ Recortar y Continuar</button>
        `;
        this.modal.appendChild(actions);

        overlay.appendChild(this.modal);
        document.body.appendChild(overlay);

        this.overlay = overlay;
    }

    calculateInitialScale() {
        // Calcular escala mínima para que la imagen cubra toda el área del canvas
        const canvasAspect = this.canvas.width / this.canvas.height;
        const imageAspect = this.image.width / this.image.height;

        if (imageAspect > canvasAspect) {
            // Imagen más ancha que el canvas, ajustar por altura
            this.minScale = this.canvas.height / this.image.height;
        } else {
            // Imagen más alta que el canvas, ajustar por ancho
            this.minScale = this.canvas.width / this.image.width;
        }

        this.scale = this.minScale;
        this.maxScale = this.minScale * 3;
    }

    centerImage() {
        const scaledWidth = this.image.width * this.scale;
        const scaledHeight = this.image.height * this.scale;

        this.offsetX = (this.canvas.width - scaledWidth) / 2;
        this.offsetY = (this.canvas.height - scaledHeight) / 2;
    }

    render() {
        // Limpiar canvas
        this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);

        // Dibujar imagen escalada y posicionada
        const scaledWidth = this.image.width * this.scale;
        const scaledHeight = this.image.height * this.scale;

        this.ctx.drawImage(
            this.image,
            this.offsetX,
            this.offsetY,
            scaledWidth,
            scaledHeight
        );

        // Dibujar overlay oscuro alrededor (si la imagen es más grande que el canvas)
        this.drawCropOverlay();
    }

    drawCropOverlay() {
        // Dibujar área semi-transparente alrededor del área de recorte
        // para indicar qué se descartará
        const scaledWidth = this.image.width * this.scale;
        const scaledHeight = this.image.height * this.scale;

        // Solo si la imagen es más grande que el canvas
        if (scaledWidth > this.canvas.width || scaledHeight > this.canvas.height) {
            this.ctx.save();
            this.ctx.fillStyle = 'rgba(0, 0, 0, 0.5)';

            // Top
            if (this.offsetY > 0) {
                this.ctx.fillRect(0, 0, this.canvas.width, this.offsetY);
            }

            // Bottom
            const bottomY = this.offsetY + scaledHeight;
            if (bottomY < this.canvas.height) {
                this.ctx.fillRect(0, bottomY, this.canvas.width, this.canvas.height - bottomY);
            }

            // Left
            if (this.offsetX > 0) {
                this.ctx.fillRect(0, 0, this.offsetX, this.canvas.height);
            }

            // Right
            const rightX = this.offsetX + scaledWidth;
            if (rightX < this.canvas.width) {
                this.ctx.fillRect(rightX, 0, this.canvas.width - rightX, this.canvas.height);
            }

            this.ctx.restore();
        }

        // Dibujar borde del área de recorte
        this.ctx.strokeStyle = '#3b82f6';
        this.ctx.lineWidth = 2;
        this.ctx.strokeRect(0, 0, this.canvas.width, this.canvas.height);
    }

    setupEvents() {
        // Zoom slider
        const slider = this.modal.querySelector('.crop-editor-zoom-slider');
        const zoomValue = this.modal.querySelector('.crop-editor-zoom-value');

        slider.addEventListener('input', (e) => {
            const percent = parseInt(e.target.value);
            this.scale = this.minScale * (percent / 100);
            zoomValue.textContent = `${percent}%`;
            this.constrainPosition();
            this.render();
        });

        // Mouse wheel zoom
        this.canvas.addEventListener('wheel', (e) => {
            e.preventDefault();
            const delta = e.deltaY > 0 ? -0.1 : 0.1;
            const newScale = Math.max(this.minScale, Math.min(this.maxScale, this.scale + delta * this.minScale));
            this.scale = newScale;

            // Actualizar slider
            const percent = Math.round((this.scale / this.minScale) * 100);
            slider.value = percent;
            zoomValue.textContent = `${percent}%`;

            this.constrainPosition();
            this.render();
        });

        // Drag
        this.canvas.addEventListener('mousedown', (e) => {
            this.isDragging = true;
            this.dragStartX = e.clientX - this.offsetX;
            this.dragStartY = e.clientY - this.offsetY;
            this.canvas.style.cursor = 'grabbing';
        });

        window.addEventListener('mousemove', (e) => {
            if (!this.isDragging) return;

            this.offsetX = e.clientX - this.dragStartX;
            this.offsetY = e.clientY - this.dragStartY;

            this.constrainPosition();
            this.render();
        });

        window.addEventListener('mouseup', () => {
            if (this.isDragging) {
                this.isDragging = false;
                this.canvas.style.cursor = 'grab';
            }
        });

        // Touch support
        this.canvas.addEventListener('touchstart', (e) => {
            e.preventDefault();
            const touch = e.touches[0];
            this.isDragging = true;
            this.dragStartX = touch.clientX - this.offsetX;
            this.dragStartY = touch.clientY - this.offsetY;
        });

        this.canvas.addEventListener('touchmove', (e) => {
            if (!this.isDragging) return;
            e.preventDefault();

            const touch = e.touches[0];
            this.offsetX = touch.clientX - this.dragStartX;
            this.offsetY = touch.clientY - this.dragStartY;

            this.constrainPosition();
            this.render();
        });

        this.canvas.addEventListener('touchend', () => {
            this.isDragging = false;
        });

        // Buttons
        this.modal.querySelector('.crop-editor-btn-cancel').addEventListener('click', () => {
            this.close();
        });

        this.modal.querySelector('.crop-editor-btn-accept').addEventListener('click', () => {
            this.cropAndSave();
        });

        // Close on overlay click
        this.overlay.addEventListener('click', (e) => {
            if (e.target === this.overlay) {
                this.close();
            }
        });

        this.canvas.style.cursor = 'grab';
    }

    constrainPosition() {
        const scaledWidth = this.image.width * this.scale;
        const scaledHeight = this.image.height * this.scale;

        // Limitar movimiento para que siempre cubra el canvas
        const maxOffsetX = 0;
        const minOffsetX = this.canvas.width - scaledWidth;

        const maxOffsetY = 0;
        const minOffsetY = this.canvas.height - scaledHeight;

        this.offsetX = Math.max(minOffsetX, Math.min(maxOffsetX, this.offsetX));
        this.offsetY = Math.max(minOffsetY, Math.min(maxOffsetY, this.offsetY));
    }

    async cropAndSave() {
        // Crear canvas final con dimensiones reales (no preview)
        const outputCanvas = document.createElement('canvas');

        // Usar dimensiones finales (puedes ajustar según necesites)
        const finalSize = 1024; // Tamaño máximo

        if (this.aspectRatio >= 1) {
            outputCanvas.width = finalSize;
            outputCanvas.height = finalSize / this.aspectRatio;
        } else {
            outputCanvas.height = finalSize;
            outputCanvas.width = finalSize * this.aspectRatio;
        }

        const outputCtx = outputCanvas.getContext('2d');

        // Calcular qué parte de la imagen original se debe extraer
        const scaleRatio = outputCanvas.width / this.canvas.width;

        const sourceX = -this.offsetX / this.scale;
        const sourceY = -this.offsetY / this.scale;
        const sourceWidth = this.canvas.width / this.scale;
        const sourceHeight = this.canvas.height / this.scale;

        // Dibujar la porción recortada
        outputCtx.drawImage(
            this.image,
            sourceX,
            sourceY,
            sourceWidth,
            sourceHeight,
            0,
            0,
            outputCanvas.width,
            outputCanvas.height
        );

        // Convertir a blob
        outputCanvas.toBlob((blob) => {
            // Crear un File object con el blob
            const croppedFile = new File(
                [blob],
                this.imageFile.name,
                { type: 'image/jpeg', lastModified: Date.now() }
            );

            // Llamar callback con el archivo recortado
            this.callback(croppedFile);

            // Cerrar modal
            this.close();
        }, 'image/jpeg', 0.92);
    }

    close() {
        if (this.overlay) {
            this.overlay.remove();
        }
    }
}
