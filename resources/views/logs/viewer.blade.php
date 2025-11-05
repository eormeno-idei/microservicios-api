<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Log Viewer</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', monospace;
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 20px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        header {
            background: #252526;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }

        h1 {
            color: #569cd6;
            margin-bottom: 15px;
            font-size: 24px;
        }

        .controls {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }

        .control-group {
            display: flex;
            gap: 5px;
            align-items: center;
        }

        label {
            color: #9cdcfe;
            font-size: 14px;
        }

        select, input, button {
            padding: 8px 12px;
            background: #3c3c3c;
            color: #d4d4d4;
            border: 1px solid #555;
            border-radius: 4px;
            font-family: inherit;
            font-size: 14px;
        }

        select:hover, input:hover, button:hover {
            border-color: #007acc;
        }

        button {
            cursor: pointer;
            transition: all 0.2s;
        }

        button:hover {
            background: #007acc;
            color: white;
        }

        button.danger {
            background: #d32f2f;
        }

        button.danger:hover {
            background: #f44336;
        }

        button.success {
            background: #388e3c;
        }

        button.success:hover {
            background: #4caf50;
        }

        .info-bar {
            background: #2d2d30;
            padding: 10px 20px;
            border-radius: 4px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12px;
        }

        .log-container {
            background: #1e1e1e;
            border: 1px solid #3c3c3c;
            border-radius: 8px;
            padding: 20px;
            min-height: 500px;
            max-height: 70vh;
            overflow-y: auto;
            font-size: 11px;
            line-height: 1.5;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }

        .log-container pre {
            margin: 0;
            white-space: pre-wrap;
            word-wrap: break-word;
        }

        .log-line {
            padding: 2px 0;
            border-left: 3px solid transparent;
            padding-left: 10px;
            margin-bottom: 4px;
        }

        .log-line.error {
            background: rgba(211, 47, 47, 0.1);
            border-left-color: #d32f2f;
            color: #f48771;
        }

        .log-line.warning {
            background: rgba(255, 152, 0, 0.1);
            border-left-color: #ff9800;
            color: #dcdcaa;
        }

        .log-line.info {
            background: rgba(33, 150, 243, 0.1);
            border-left-color: #2196f3;
            color: #9cdcfe;
        }

        .log-line.debug {
            color: #808080;
        }

        .loading {
            text-align: center;
            padding: 40px;
            color: #569cd6;
        }

        .error-message {
            background: rgba(211, 47, 47, 0.2);
            color: #f48771;
            padding: 15px;
            border-radius: 4px;
            border-left: 4px solid #d32f2f;
        }

        .file-list {
            margin-top: 10px;
            font-size: 12px;
        }

        .file-item {
            padding: 5px;
            margin: 2px 0;
            background: #2d2d30;
            border-radius: 3px;
        }

        .auto-refresh {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .status-indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #4caf50;
            animation: pulse 2s infinite;
        }

        .status-indicator.inactive {
            background: #757575;
            animation: none;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        ::-webkit-scrollbar {
            width: 10px;
            height: 10px;
        }

        ::-webkit-scrollbar-track {
            background: #1e1e1e;
        }

        ::-webkit-scrollbar-thumb {
            background: #424242;
            border-radius: 5px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>🔍 Log Viewer</h1>
            <div class="controls">
                <div class="control-group">
                    <label for="logFile">Archivo:</label>
                    <select id="logFile" onchange="loadLogs()">
                        @foreach($logFiles as $file)
                            <option value="{{ $file['name'] }}" {{ $file['name'] === $selectedFile ? 'selected' : '' }}>
                                {{ $file['name'] }} ({{ $file['size'] }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="control-group">
                    <label for="lines">Líneas:</label>
                    <input type="number" id="lines" value="1000" min="10" max="5000" step="10" style="width: 80px;">
                </div>

                <div class="control-group">
                    <label for="search">Buscar:</label>
                    <input type="text" id="search" placeholder="Filtrar logs..." style="width: 200px;">
                </div>

                <button onclick="loadLogs()" class="success">🔄 Actualizar</button>
                <button onclick="downloadLog()">⬇️ Descargar</button>
                <button onclick="clearLog()" class="danger">🗑️ Limpiar</button>

                <div class="control-group auto-refresh">
                    <label>
                        <input type="checkbox" id="autoRefresh" onchange="toggleAutoRefresh()">
                        Auto-actualizar (5s)
                    </label>
                    <div class="status-indicator inactive" id="statusIndicator"></div>
                </div>
            </div>
        </header>

        <div class="info-bar">
            <div id="fileInfo">Selecciona un archivo de log</div>
            <div id="lastUpdate">Última actualización: --</div>
        </div>

        @if(session('success'))
            <div class="success-message" style="background: rgba(76, 175, 80, 0.2); color: #4caf50; padding: 15px; border-radius: 4px; margin-bottom: 15px; border-left: 4px solid #4caf50;">
                ✅ {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="error-message" style="background: rgba(211, 47, 47, 0.2); color: #f48771; padding: 15px; border-radius: 4px; margin-bottom: 15px; border-left: 4px solid #d32f2f;">
                ❌ {{ session('error') }}
            </div>
        @endif

        <div class="log-container" id="logContainer">
            <div class="loading">Cargando logs...</div>
        </div>

        <!-- Hidden form for CSRF token (fallback method) -->
        <form id="clearLogForm" method="POST" action="/logs/clear" style="display: none;">
            @csrf
            <input type="hidden" name="file" id="clearLogFile">
        </form>
    </div>

    <script>
        let autoRefreshInterval = null;
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        // Verify CSRF token on load
        document.addEventListener('DOMContentLoaded', function() {
            if (!csrfToken) {
                console.error('CSRF token not found in meta tag!');
                alert('⚠️ Error de configuración: No se encontró el token CSRF');
            } else {
                console.log('CSRF token loaded successfully');
            }
            loadLogs();
        });

        // Trigger search on Enter
        document.getElementById('search').addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                loadLogs();
            }
        });

        async function loadLogs() {
            const file = document.getElementById('logFile').value;
            const lines = document.getElementById('lines').value;
            const search = document.getElementById('search').value;
            const container = document.getElementById('logContainer');

            container.innerHTML = '<div class="loading">Cargando logs...</div>';

            try {
                const response = await fetch(`/logs/content?file=${file}&lines=${lines}&search=${encodeURIComponent(search)}`, {
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                });
                const data = await response.json();

                if (data.error) {
                    container.innerHTML = `<div class="error-message">❌ ${data.error}</div>`;
                    return;
                }

                displayLogs(data.content);
                updateInfo(data);
                
                // Auto-scroll to bottom
                container.scrollTop = container.scrollHeight;
            } catch (error) {
                container.innerHTML = `<div class="error-message">❌ Error al cargar logs: ${error.message}</div>`;
            }
        }

        function displayLogs(content) {
            const container = document.getElementById('logContainer');
            const lines = content.split('\n');
            
            let html = '<pre>';
            lines.forEach(line => {
                const className = getLogLineClass(line);
                html += `<div class="log-line ${className}">${escapeHtml(line)}</div>`;
            });
            html += '</pre>';
            
            container.innerHTML = html;
        }

        function getLogLineClass(line) {
            const lowerLine = line.toLowerCase();
            if (lowerLine.includes('error') || lowerLine.includes('exception') || lowerLine.includes('fatal')) {
                return 'error';
            }
            if (lowerLine.includes('warning') || lowerLine.includes('warn')) {
                return 'warning';
            }
            if (lowerLine.includes('info')) {
                return 'info';
            }
            if (lowerLine.includes('debug')) {
                return 'debug';
            }
            return '';
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function updateInfo(data) {
            document.getElementById('fileInfo').textContent = 
                `Tamaño: ${data.fileSize} | Líneas totales: ${data.totalLines || 'N/A'}`;
            document.getElementById('lastUpdate').textContent = 
                `Última actualización: ${new Date().toLocaleTimeString()}`;
        }

        async function downloadLog() {
            const file = document.getElementById('logFile').value;
            window.location.href = `/logs/download?file=${file}`;
        }

        async function clearLog() {
            const file = document.getElementById('logFile').value;
            const container = document.getElementById('logContainer');
            const originalContent = container.innerHTML;
            
            container.innerHTML = '<div class="loading">Limpiando log...</div>';
            
            try {
                const response = await fetch('/logs/clear', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ file })
                });

                console.log('Response status:', response.status);

                if (response.ok) {
                    const data = await response.json();
                    console.log('Response data:', data);
                    
                    if (data.success) {
                        loadLogs();
                    } else {
                        container.innerHTML = originalContent;
                        alert('❌ Error: ' + (data.error || 'Error desconocido'));
                    }
                } else {
                    container.innerHTML = originalContent;
                    const text = await response.text();
                    console.error('Error response:', text);
                    alert('❌ Error del servidor (código ' + response.status + ')');
                }
            } catch (error) {
                console.error('Error clearing log:', error);
                container.innerHTML = originalContent;
                alert('❌ Error al limpiar el log: ' + error.message);
            }
        }

        function toggleAutoRefresh() {
            const checkbox = document.getElementById('autoRefresh');
            const indicator = document.getElementById('statusIndicator');
            
            if (checkbox.checked) {
                autoRefreshInterval = setInterval(loadLogs, 5000);
                indicator.classList.remove('inactive');
            } else {
                if (autoRefreshInterval) {
                    clearInterval(autoRefreshInterval);
                    autoRefreshInterval = null;
                }
                indicator.classList.add('inactive');
            }
        }

        // Clean up on page unload
        window.addEventListener('beforeunload', function() {
            if (autoRefreshInterval) {
                clearInterval(autoRefreshInterval);
            }
        });
    </script>
</body>
</html>
