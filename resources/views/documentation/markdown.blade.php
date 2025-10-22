<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} - Laravel API Client</title>

    <!-- CSS Unificado para tema oscuro de documentación -->
    <link rel="stylesheet" href="/css/documentation-dark-theme.css">

    <!-- Marked.js - Renderizador de Markdown -->
    <script src="https://cdn.jsdelivr.net/npm/marked@11.1.1/marked.min.js"></script>

    <!-- Highlight.js para syntax highlighting en código -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github-dark.min.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📚 {{ $title }}</h1>
            <a href="{{ $backUrl }}" class="back-button">← Volver</a>
        </div>
        <div class="content">
            <div class="markdown-content" id="markdown-content" style="white-space: pre-wrap; display: none;">{{ $content }}</div>
            <div id="rendered-content" class="markdown-content"></div>
        </div>
    </div>    <script>
        // Configuración y renderizado con Marked.js
        document.addEventListener('DOMContentLoaded', function() {
            const content = document.getElementById('markdown-content');
            const renderedContent = document.getElementById('rendered-content');

            // Obtener el contenido raw del Markdown
            let markdownText = content.textContent || content.innerText;

            // console.log('Raw markdown length:', markdownText.length);
            // console.log('First 200 chars:', markdownText.substring(0, 200));
            // console.log('Starts with #?', markdownText.trim().startsWith('#'));

            // Asegurar que tenemos el contenido en formato raw
            if (markdownText.trim().length === 0) {
                console.error('No markdown content found');
                renderedContent.innerHTML = '<div class="error-message">No se encontró contenido para renderizar</div>';
                return;
            }

            // console.log('Markdown text to parse:', markdownText.substring(0, 100) + '...');

            // Configurar Marked.js
            marked.setOptions({
                highlight: function(code, lang) {
                    // Si highlight.js reconoce el lenguaje, lo resalta
                    if (lang && hljs.getLanguage(lang)) {
                        try {
                            return hljs.highlight(code, { language: lang }).value;
                        } catch (err) {
                            console.warn('Error highlighting code:', err);
                        }
                    }
                    // Si no, usa auto-detección
                    try {
                        return hljs.highlightAuto(code).value;
                    } catch (err) {
                        console.warn('Error auto-highlighting code:', err);
                        return code;
                    }
                },
                langPrefix: 'hljs language-',
                breaks: true,
                gfm: true,
                headerIds: true,
                mangle: false,
                sanitize: false
            });

            // Configurar un renderer personalizado para mantener el estilo del tema oscuro
            const renderer = new marked.Renderer();

            // Personalizar el renderizado de headers
            renderer.heading = function(text, level, raw) {
                const id = raw.toLowerCase().replace(/[^\w]+/g, '-');
                return `<h${level} id="${id}" class="markdown-heading">${text}</h${level}>`;
            };

            // Personalizar el renderizado de código inline
            renderer.codespan = function(code) {
                return `<code class="inline-code">${code}</code>`;
            };

            // Personalizar el renderizado de enlaces
            renderer.link = function(href, title, text) {
                const titleAttr = title ? ` title="${title}"` : '';
                let target = '';
                let className = '';

                // Enlaces externos
                if (href.startsWith('http') && !href.includes(window.location.hostname)) {
                    target = ' target="_blank" rel="noopener noreferrer"';
                    className = ' class="external-link"';
                }
                // Enlaces internos (anchors)
                else if (href.startsWith('#')) {
                    className = ' class="internal-link"';
                }
                // Enlaces de email
                else if (href.startsWith('mailto:')) {
                    className = ' class="email-link"';
                }

                return `<a href="${href}"${titleAttr}${target}${className}>${text}</a>`;
            };

            // Personalizar el renderizado de tablas
            renderer.table = function(header, body) {
                return `<div class="table-wrapper"><table><thead>${header}</thead><tbody>${body}</tbody></table></div>`;
            };

            // Personalizar blockquotes
            renderer.blockquote = function(quote) {
                return `<blockquote class="markdown-blockquote">${quote}</blockquote>`;
            };

            marked.use({ renderer });

            // Renderizar el Markdown
            try {
                const html = marked.parse(markdownText);
                // console.log('Rendered HTML:', html.substring(0, 200) + '...');

                renderedContent.innerHTML = html;
                renderedContent.classList.add('markdown-content');

                // Aplicar highlight.js a los bloques de código que no se procesaron
                renderedContent.querySelectorAll('pre code').forEach((block) => {
                    if (!block.classList.contains('hljs')) {
                        hljs.highlightElement(block);
                    }
                });

                // Añadir clases para el tema oscuro
                renderedContent.querySelectorAll('code:not(.hljs)').forEach((code) => {
                    if (code.parentElement.tagName !== 'PRE') {
                        code.classList.add('inline-code');
                    }
                });

                // Mejorar navegación de enlaces internos
                renderedContent.querySelectorAll('a.internal-link').forEach((link) => {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        const targetId = this.getAttribute('href').substring(1);
                        const targetElement = document.getElementById(targetId);

                        if (targetElement) {
                            // Smooth scroll al elemento
                            targetElement.scrollIntoView({
                                behavior: 'smooth',
                                block: 'start'
                            });

                            // Destacar temporalmente el elemento objetivo
                            targetElement.style.backgroundColor = 'rgba(0, 212, 170, 0.2)';
                            targetElement.style.transition = 'background-color 0.3s ease';

                            setTimeout(() => {
                                targetElement.style.backgroundColor = '';
                            }, 2000);
                        }
                    });
                });

            } catch (error) {
                console.error('Error rendering Markdown:', error);
                renderedContent.innerHTML = `<div class="error-message">Error al renderizar el contenido Markdown: ${error.message}</div>`;
            }
        });
    </script>
</body>
</html>
