<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class DocumentationController extends Controller
{
    /**
     * Mostrar la documentación completa del API
     */
    public function apiCompleteDocs()
    {
        $readmePath = base_path('docs/API_COMPLETE_DOCUMENTATION.md');

        if (!File::exists($readmePath)) {
            return response()->view('documentation.not-found', [
                'title' => 'Documentación del API',
                'file' => 'API_COMPLETE_DOCUMENTATION.md'
            ], 404);
        }

        $content = File::get($readmePath);

        return view('documentation.markdown', [
            'title' => 'Documentación del API',
            'content' => $content,
            'backUrl' => env('API_CLIENT_ROUTE', '/api-client')
        ]);
    }

    /**
     * Mostrar el resumen completo de implementaciones
     */
    public function implementationSummaryDocs()
    {
        $readmePath = base_path('docs/IMPLEMENTATION_COMPLETE_SUMMARY.md');

        if (!File::exists($readmePath)) {
            return response()->view('documentation.not-found', [
                'title' => 'Resumen de Implementaciones',
                'file' => 'IMPLEMENTATION_COMPLETE_SUMMARY.md'
            ], 404);
        }

        $content = File::get($readmePath);

        return view('documentation.markdown', [
            'title' => 'Resumen Completo de Implementaciones',
            'content' => $content,
            'backUrl' => env('API_CLIENT_ROUTE', '/api-client')
        ]);
    }

    /**
     * Mostrar la documentación de componentes técnicos
     */
    public function technicalComponentsDocs()
    {
        $readmePath = base_path('docs/TECHNICAL_COMPONENTS_README.md');

        if (!File::exists($readmePath)) {
            return response()->view('documentation.not-found', [
                'title' => 'Componentes',
                'file' => 'TECHNICAL_COMPONENTS_README.md'
            ], 404);
        }

        $content = File::get($readmePath);

        return view('documentation.markdown', [
            'title' => 'Documentación de Componentes',
            'content' => $content,
            'backUrl' => env('API_CLIENT_ROUTE', '/api-client')
        ]);
    }

    /**
     * Mostrar la guía de personalización de emails
     */
    public function emailCustomizationDocs()
    {
        $readmePath = base_path('docs/EMAIL_CUSTOMIZATION_GUIDE.md');

        if (!File::exists($readmePath)) {
            return response()->view('documentation.not-found', [
                'title' => 'Guía de Personalización de Emails',
                'file' => 'EMAIL_CUSTOMIZATION_GUIDE.md'
            ], 404);
        }

        $content = File::get($readmePath);

        return view('documentation.markdown', [
            'title' => 'Guía de Personalización de Emails',
            'content' => $content,
            'backUrl' => env('API_CLIENT_ROUTE', '/api-client')
        ]);
    }

    /**
     * Mostrar ejemplos de archivos y uploads
     */
    public function fileUploadExamplesDocs()
    {
        $readmePath = base_path('docs/FILE_UPLOAD_EXAMPLES.md');

        if (!File::exists($readmePath)) {
            return response()->view('documentation.not-found', [
                'title' => 'Ejemplos de Upload de Archivos',
                'file' => 'FILE_UPLOAD_EXAMPLES.md'
            ], 404);
        }

        $content = File::get($readmePath);

        return view('documentation.markdown', [
            'title' => 'Ejemplos y Casos de Uso - Upload de Archivos',
            'content' => $content,
            'backUrl' => env('API_CLIENT_ROUTE', '/api-client')
        ]);
    }

    /**
     * Índice de toda la documentación disponible
     */
    public function docsIndex()
    {
        $availableDocs = [
            [
                'title' => 'Documentación del API',
                'description' => 'Documentación unificada con todos los endpoints, ejemplos de cliente y manejo de errores',
                'route' => 'docs.api-complete',
                'category' => 'Principal',
                'priority' => 1
            ],
            [
                'title' => 'Resumen de Implementaciones',
                'description' => 'Estado completo del proyecto, testing y configuraciones',
                'route' => 'docs.implementation-summary',
                'category' => 'Principal',
                'priority' => 2
            ],
            [
                'title' => 'Componentes Técnicos',
                'description' => 'Arquitectura CSS, Markdown rendering y configuraciones técnicas',
                'route' => 'docs.technical-components',
                'category' => 'Principal',
                'priority' => 3
            ],
            [
                'title' => 'Personalización de Emails',
                'description' => 'Guía detallada para personalizar templates y estilos de emails',
                'route' => 'docs.email-customization',
                'category' => 'Especializado',
                'priority' => 4
            ],
            [
                'title' => 'Ejemplos de Upload de Archivos',
                'description' => 'Casos de uso prácticos, scripts de testing y troubleshooting',
                'route' => 'docs.file-upload-examples',
                'category' => 'Especializado',
                'priority' => 5
            ]
        ];

        return view('documentation.index', [
            'title' => 'Índice de Documentación',
            'docs' => collect($availableDocs)->groupBy('category'),
            'backUrl' => env('API_CLIENT_ROUTE', '/api-client')
        ]);
    }
}
