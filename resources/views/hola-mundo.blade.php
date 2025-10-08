<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hola Mundo - Laravel</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .container {
            text-align: center;
            padding: 2rem;
            position: relative;
        }

        .hello-world {
            font-size: 4rem;
            font-weight: bold;
            color: #fff;
            text-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            margin-bottom: 1rem;
            animation: fadeInUp 1s ease-out;
            background: linear-gradient(45deg, #fff, #e3f2fd);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .subtitle {
            font-size: 1.5rem;
            color: #f8f9fa;
            margin-bottom: 2rem;
            opacity: 0.9;
            animation: fadeInUp 1s ease-out 0.3s both;
        }

        .description {
            font-size: 1.1rem;
            color: #e9ecef;
            max-width: 600px;
            margin: 0 auto 3rem;
            line-height: 1.6;
            animation: fadeInUp 1s ease-out 0.6s both;
        }

        .highlight {
            color: #ffd700;
            font-weight: bold;
            text-shadow: 0 2px 4px rgba(255, 215, 0, 0.3);
            background: linear-gradient(45deg, #ffd700, #ffed4e);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: glow 2s ease-in-out infinite alternate;
        }

        @keyframes glow {
            from {
                filter: drop-shadow(0 0 5px rgba(255, 215, 0, 0.5));
            }
            to {
                filter: drop-shadow(0 0 10px rgba(255, 215, 0, 0.8));
            }
        }

        .btn-container {
            animation: fadeInUp 1s ease-out 0.9s both;
        }

        .btn {
            display: inline-block;
            padding: 12px 30px;
            margin: 0 10px;
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            text-decoration: none;
            border-radius: 25px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            font-weight: 500;
        }

        .btn:hover {
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.5);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }

        .btn-primary {
            background: rgba(102, 126, 234, 0.8);
            border-color: rgba(102, 126, 234, 1);
        }

        .btn-primary:hover {
            background: rgba(102, 126, 234, 1);
        }

        /* Partículas flotantes */
        .particle {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }

        .particle:nth-child(1) {
            width: 20px;
            height: 20px;
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }

        .particle:nth-child(2) {
            width: 15px;
            height: 15px;
            top: 60%;
            left: 85%;
            animation-delay: 1s;
        }

        .particle:nth-child(3) {
            width: 25px;
            height: 25px;
            top: 80%;
            left: 15%;
            animation-delay: 2s;
        }

        .particle:nth-child(4) {
            width: 18px;
            height: 18px;
            top: 30%;
            left: 80%;
            animation-delay: 3s;
        }

        .particle:nth-child(5) {
            width: 22px;
            height: 22px;
            top: 70%;
            left: 70%;
            animation-delay: 4s;
        }

        .laravel-logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 2rem;
            opacity: 0.9;
            animation: fadeInUp 1s ease-out 0.1s both;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0px) rotate(0deg);
            }
            50% {
                transform: translateY(-20px) rotate(180deg);
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hello-world {
                font-size: 2.5rem;
            }

            .subtitle {
                font-size: 1.2rem;
            }

            .description {
                font-size: 1rem;
                padding: 0 1rem;
            }

            .btn {
                display: block;
                margin: 10px 0;
                width: 200px;
                margin-left: auto;
                margin-right: auto;
            }
        }

        /* Efectos adicionales */
        .sparkle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: #fff;
            border-radius: 50%;
            animation: sparkle 2s linear infinite;
        }

        @keyframes sparkle {
            0%, 100% {
                opacity: 0;
                transform: scale(0);
            }
            50% {
                opacity: 1;
                transform: scale(1);
            }
        }
    </style>
</head>
<body>
    <!-- Partículas de fondo -->
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>

    <!-- Contenido principal -->
    <div class="container">
        <!-- Logo de Laravel -->
        <div class="laravel-logo">
            <svg viewBox="0 0 50 52" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M49.626 11.564a.809.809 0 0 1 .028.209v10.972a.8.8 0 0 1-.402.694l-9.209 5.302V39.25c0 .286-.152.55-.4.694L20.42 51.01c-.044.025-.092.041-.14.058-.018.006-.035.017-.054.022a.805.805 0 0 1-.41 0c-.022-.006-.042-.018-.063-.026-.044-.016-.09-.03-.132-.054L.402 39.944A.801.801 0 0 1 0 39.25V6.334c0-.072.016-.142.028-.21.017-.099.048-.193.098-.283.013-.023.022-.05.037-.072.054-.083.118-.157.19-.22.023-.02.042-.047.068-.065.095-.071.205-.124.32-.151.037-.009.08-.009.118-.02L12.058.571a.8.8 0 0 1 .804 0L24.061 5.34c.038.011.081.011.118.02.115.027.225.08.32.151.026.018.045.045.068.065.072.063.136.137.19.22.015.022.024.049.037.072.05.09.081.184.098.283.012.068.028.138.028.21v26.394l7.496-4.317V15.765c0-.072.016-.142.028-.21.017-.099.048-.193.098-.283.013-.023.022-.05.037-.072.054-.083.118-.157.19-.22.023-.02.042-.047.068-.065.095-.071.205-.124.32-.151.037-.009.08-.009.118-.02l11.199-4.769a.8.8 0 0 1 .804 0l11.199 4.769c.038.011.081.011.118.02.115.027.225.08.32.151.026.018.045.045.068.065.072.063.136.137.19.22.015.022.024.049.037.072.05.09.081.184.098.283.012.068.028.138.028.21z" fill="#fff" opacity="0.9"/>
            </svg>
        </div>

        <h1 class="hello-world">¡Hola Mundo!</h1>
        <p class="subtitle">Bienvenido a Laravel</p>
        <p class="description">
            Hola <span class="highlight">{{ $datos['nombre'] }}</span>! Esta es tu primera página creada con Laravel y Blade.
            Un framework PHP elegante y expresivo que hace que el desarrollo web sea un placer.
            ¡Comienza tu viaje de desarrollo aquí! (a pesar que ya tienes {{ $datos['edad'] }} años).
        </p>

        <div class="btn-container">
            <a href="{{ route('welcome') }}" class="btn btn-primary">Ir al Inicio</a>
            <a href="https://laravel.com/docs" target="_blank" class="btn">Documentación</a>
        </div>
    </div>

    <!-- Efectos de brillo -->
    <script>
        // Crear efectos de brillo aleatorios
        function createSparkle() {
            const sparkle = document.createElement('div');
            sparkle.className = 'sparkle';
            sparkle.style.left = Math.random() * 100 + '%';
            sparkle.style.top = Math.random() * 100 + '%';
            document.body.appendChild(sparkle);

            setTimeout(() => {
                sparkle.remove();
            }, 2000);
        }

        // Crear brillos cada 800ms
        setInterval(createSparkle, 800);

        // Efecto de movimiento del mouse
        document.addEventListener('mousemove', (e) => {
            const particles = document.querySelectorAll('.particle');
            particles.forEach((particle, index) => {
                const speed = (index + 1) * 0.01;
                const x = (e.clientX * speed) / 50;
                const y = (e.clientY * speed) / 50;
                particle.style.transform = `translate(${x}px, ${y}px)`;
            });
        });
    </script>
</body>
</html>
