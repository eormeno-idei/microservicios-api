<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Demo - {{ ucfirst(str_replace('-', ' ', $demo)) }}</title>
    <link rel="stylesheet" href="{{ asset('css/ui-components.css') }}">
    <link rel="stylesheet" href="{{ asset('css/uploader-component.css') }}">
</head>
<body>
    <header id="top-menu-bar">
        <div id="menu"></div>
    </header>
    <main id="main"></main>
    <div id="modal-overlay" class="modal-overlay hidden">
        <div id="modal" class="modal-container"></div>
    </div>
    {{-- <button id="reset-btn" onclick="location.href='?reset=true'">Reset</button> --}}

    @php
        // Obtener todos los parámetros de ruta automáticamente
        $routeParams = request()->route()->parameters();

        // Crear un array con prefijo "route_" para diferenciarlos de query params
        $prefixedRouteParams = [];
        foreach ($routeParams as $key => $value) {
            $prefixedRouteParams["route_$key"] = $value;
        }

        // Combinar con params manuales si existen (para compatibilidad)
        $allParams = array_merge($prefixedRouteParams, $params ?? []);
    @endphp

    <script>
        // Pass service name from Laravel to JavaScript
        window.DEMO_NAME = '{{ $demo }}';
        window.RESET_DEMO = {{ $reset ? 'true' : 'false' }};
        window.MENU_SERVICE = 'demo-menu';
        window.PARAMS = @json($allParams);
        window.QUERY_PARAMS = new URLSearchParams(window.location.search);
    </script>
    <script src="{{ asset('js/ui-renderer.js') }}"></script>
    <script src="{{ asset('js/uploader-component.js') }}"></script>
</body>
</html>
