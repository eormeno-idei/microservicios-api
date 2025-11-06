<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Demo - {{ ucfirst(str_replace('-', ' ', $demo)) }}</title>
    <link rel="stylesheet" href="css/ui-components.css">
</head>
<body>
    <header id="top-menu-bar">
        <div id="menu"></div>
        <button id="reset-btn" onclick="location.href='?reset=true'">Reset</button>
    </header>
    <main id="main"></main>
    <div id="modal-overlay" class="modal-overlay hidden">
        <div id="modal" class="modal-container"></div>
    </div>

    <script>
        // Pass demo name from Laravel to JavaScript
        window.DEMO_NAME = '{{ $demo }}';
        window.RESET_DEMO = {{ $reset ? 'true' : 'false' }};
        window.MENU_SERVICE = 'demo-menu';
    </script>
    <script src="js/ui-renderer.js"></script>
</body>
</html>
