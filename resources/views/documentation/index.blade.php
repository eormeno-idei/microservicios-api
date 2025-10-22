<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} - Laravel API Client</title>

    <!-- CSS Unificado para tema oscuro de documentación -->
    <link rel="stylesheet" href="css/documentation-dark-theme.css">

    <style>
        .docs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .doc-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius-lg);
            padding: 25px;
            transition: all var(--transition-normal);
            text-decoration: none;
            color: var(--text-primary);
            display: block;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-md);
        }

        .doc-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--primary-light));
            transform: scaleX(0);
            transform-origin: left;
            transition: transform var(--transition-normal);
        }

        .doc-card:hover {
            border-color: var(--primary-color);
            background: var(--bg-panel);
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(0, 212, 170, 0.15), var(--shadow-lg);
        }

        .doc-card:hover::before {
            transform: scaleX(1);
        }

        .doc-card:focus {
            outline: 2px solid var(--primary-color);
            outline-offset: 2px;
        }

        .doc-title {
            font-size: var(--font-size-xl);
            font-weight: var(--font-weight-semibold);
            margin-bottom: 12px;
            color: var(--text-light);
            transition: color var(--transition-fast);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .doc-card:hover .doc-title {
            color: var(--primary-light);
        }

        .doc-title::before {
            content: '📄';
            font-size: 1.2em;
            opacity: 0.8;
        }

        .doc-description {
            color: var(--text-secondary);
            line-height: 1.6;
            margin-bottom: 15px;
            font-size: var(--font-size-base);
            text-align: justify;
        }

        .doc-category {
            display: inline-flex;
            align-items: center;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: var(--text-light);
            padding: 6px 12px;
            border-radius: var(--border-radius-xl);
            font-size: var(--font-size-xs);
            font-weight: var(--font-weight-medium);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 4px rgba(0, 212, 170, 0.2);
            transition: all var(--transition-fast);
        }

        .doc-category::before {
            content: '🏷️';
            margin-right: 4px;
            font-size: 0.9em;
        }

        .doc-card:hover .doc-category {
            transform: scale(1.05);
            box-shadow: 0 4px 8px rgba(0, 212, 170, 0.3);
        }

        .category-section {
            margin-bottom: 40px;
            padding: 20px;
            background: rgba(26, 32, 44, 0.3);
            border-radius: var(--border-radius-md);
            border: 1px solid var(--border-color);
        }

        .category-title {
            font-size: var(--font-size-xxl);
            font-weight: var(--font-weight-bold);
            margin-bottom: 20px;
            color: var(--text-light);
            border-bottom: 3px solid var(--primary-color);
            padding-bottom: 12px;
            position: relative;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .category-title::after {
            content: '';
            position: absolute;
            bottom: -3px;
            left: 0;
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, var(--primary-light), transparent);
            border-radius: 2px;
        }

        .stats-summary {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius-lg);
            padding: 30px;
            margin-bottom: 40px;
            box-shadow: var(--shadow-lg);
            position: relative;
            overflow: hidden;
        }

        .stats-summary::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--info-color), var(--primary-color), var(--success-color));
        }

        .stats-summary h2 {
            color: var(--text-light);
            font-size: var(--font-size-xl);
            font-weight: var(--font-weight-semibold);
            margin-bottom: 20px;
            text-align: center;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 2em;
            font-weight: bold;
            color: var(--success);
        }

        .stat-label {
            color: var(--text-secondary);
            font-size: 0.9em;
        }

        /* Efectos de animación para las tarjetas */
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .doc-card {
            animation: slideInUp 0.6s ease-out;
        }

        .doc-card:nth-child(odd) {
            animation-delay: 0.1s;
        }

        .doc-card:nth-child(even) {
            animation-delay: 0.2s;
        }

        /* Estilos para párrafos de navegación */
        .stats-summary p {
            margin: 10px 0;
            line-height: 1.6;
        }

        .stats-summary p[style*="margin-left"] {
            background: rgba(0, 212, 170, 0.1);
            padding: 8px 16px;
            border-radius: var(--border-radius-sm);
            border-left: 3px solid var(--primary-color);
            margin: 8px 0 8px 20px;
            font-style: italic;
        }

        .stats-summary strong {
            color: var(--primary-light);
            font-weight: var(--font-weight-semibold);
        }

        /* Responsive mejorado */
        @media (max-width: 768px) {
            .docs-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .doc-card {
                padding: 20px;
            }

            .category-section {
                padding: 15px;
                margin-bottom: 25px;
            }

            .category-title {
                font-size: var(--font-size-lg);
                margin-bottom: 15px;
            }

            .stats-summary {
                padding: 20px;
                margin-bottom: 25px;
            }
        }

        @media (max-width: 480px) {
            .doc-card {
                padding: 15px;
            }

            .doc-title {
                font-size: var(--font-size-md);
            }

            .doc-description {
                font-size: var(--font-size-sm);
            }

            .category-section {
                padding: 10px;
            }

            .stats-summary {
                padding: 15px;
            }

            .stats-summary p[style*="margin-left"] {
                margin-left: 10px !important;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📚 {{ $title }}</h1>
            <a href="{{ $backUrl }}" class="back-button">← Volver</a>
        </div>

        <div class="content">
            <!-- Documentación por categorías -->
            @foreach($docs as $category => $categoryDocs)
                <div class="category-section">
                    <h2 class="category-title">
                        @if($category === 'Principal')
                            🎯 Documentación Principal
                        @elseif($category === 'Especializado')
                            📑 Guías Especializadas
                        @else
                            📋 Meta-Documentación
                        @endif
                    </h2>

                    <div class="docs-grid">
                        @foreach($categoryDocs as $doc)
                            <a href="{{ route($doc['route']) }}" class="doc-card">
                                <div class="doc-title">{{ $doc['title'] }}</div>
                                <div class="doc-description">{{ $doc['description'] }}</div>
                                <span class="doc-category">{{ $doc['category'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <!-- Guía de navegación -->
            <div class="stats-summary">
                <h2>🧭 Guía de Navegación Recomendada</h2>
                <div style="margin-top: 15px;">
                    <p><strong>📚 Para desarrolladores nuevos:</strong></p>
                    <p style="margin-left: 20px; color: var(--text-secondary);">
                        README.md → Documentación Completa del API → Resumen de Implementaciones
                    </p>

                    <p style="margin-top: 15px;"><strong>🔧 Para casos específicos:</strong></p>
                    <p style="margin-left: 20px; color: var(--text-secondary);">
                        Personalización de Emails → Ejemplos de Upload de Archivos → Componentes Técnicos
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
