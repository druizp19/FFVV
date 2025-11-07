<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Medifarma</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f8fafc;
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1fr 1fr;
            overflow: hidden;
        }

        /* Lado izquierdo - Gradiente */
        .left-side {
            background: linear-gradient(135deg, #f03535ff 0%, #7e4febff 50%, #0162ffff 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 60px;
            position: relative;
            overflow: hidden;
        }

        .left-side::before {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            top: -100px;
            left: -100px;
            animation: float 20s ease-in-out infinite;
        }

        .left-side::after {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.08) 0%, transparent 70%);
            bottom: -80px;
            right: -80px;
            animation: float 15s ease-in-out infinite reverse;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(30px, 30px); }
        }

        .brand-content {
            position: relative;
            z-index: 1;
            text-align: center;
            color: white;
        }

        .brand-logo {
            margin-bottom: 40px;
        }

        .brand-logo img {
            max-width: 280px;
            height: auto;
            filter: brightness(0) invert(1);
            drop-shadow(0 10px 30px rgba(0, 0, 0, 0.2));
        }

        .brand-title {
            font-size: 48px;
            font-weight: 700;
            margin-bottom: 16px;
            letter-spacing: -1px;
        }

        .brand-subtitle {
            font-size: 20px;
            font-weight: 400;
            opacity: 0.95;
            letter-spacing: 0.5px;
        }

        /* Lado derecho - Formulario */
        .right-side {
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 60px;
        }

        .login-box {
            width: 100%;
            max-width: 440px;
            animation: slideIn 0.6s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .login-header {
            margin-bottom: 48px;
        }

        .login-header h1 {
            font-size: 32px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 8px;
        }

        .login-header p {
            font-size: 16px;
            color: #64748b;
        }

        .alert {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 32px;
            font-size: 14px;
            font-weight: 500;
            animation: fadeIn 0.4s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .alert-error {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        .alert-success {
            background: #f0fdf4;
            color: #16a34a;
            border: 1px solid #bbf7d0;
        }

        .login-button {
            width: 100%;
            background: white;
            border: 2px solid #e2e8f0;
            padding: 18px 24px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            color: #1e293b;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            transition: all 0.3s ease;
            text-decoration: none;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .login-button:hover {
            border-color: #8b5cf6;
            background: #faf5ff;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(139, 92, 246, 0.15);
        }

        .login-button:active {
            transform: translateY(0);
        }

        .microsoft-icon {
            width: 24px;
            height: 24px;
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 32px 0;
            color: #94a3b8;
            font-size: 14px;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e2e8f0;
        }

        .divider span {
            padding: 0 16px;
        }

        .info-box {
            background: linear-gradient(135deg, #faf5ff 0%, #eff6ff 100%);
            border: 1px solid #e9d5ff;
            border-radius: 12px;
            padding: 20px;
            margin-top: 32px;
        }

        .info-box p {
            font-size: 14px;
            color: #6b21a8;
            line-height: 1.6;
            margin: 0;
        }

        .footer {
            margin-top: 48px;
            text-align: center;
            color: #94a3b8;
            font-size: 13px;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            body {
                grid-template-columns: 1fr;
            }

            .left-side {
                display: none;
            }

            .right-side {
                padding: 40px 24px;
            }
        }

        @media (max-width: 480px) {
            .login-header h1 {
                font-size: 28px;
            }

            .login-box {
                max-width: 100%;
            }

            .login-button {
                padding: 16px 20px;
                font-size: 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Lado izquierdo con gradiente -->
    <div class="left-side">
        <div class="brand-content">
            <div class="brand-logo">
                <img src="{{ asset('images/logo-medifarma-Photoroom.png') }}" alt="Medifarma">
            </div>
            <h1 class="brand-title">Fuerza de Venta</h1>
            <p class="brand-subtitle">Sistema de Gestión</p>
        </div>
    </div>

    <!-- Lado derecho con formulario -->
    <div class="right-side">
        <div class="login-box">
            <div class="login-header">
                <h1>Bienvenido</h1>
                <p>Inicia sesión para continuar</p>
            </div>

            @if(session('error'))
                <div class="alert alert-error">
                    {{ session('error') }}
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <a href="{{ route('azure.login') }}" class="login-button">
                <svg class="microsoft-icon" viewBox="0 0 23 23">
                    <path fill="#f35325" d="M0 0h11v11H0z"/>
                    <path fill="#81bc06" d="M12 0h11v11H12z"/>
                    <path fill="#05a6f0" d="M0 12h11v11H0z"/>
                    <path fill="#ffba08" d="M12 12h11v11H12z"/>
                </svg>
                Iniciar sesión con Microsoft
            </a>

            <div class="divider">
                <span>Acceso seguro</span>
            </div>

            <div class="info-box">
                <p>
                    <strong>🔒 Autenticación corporativa</strong><br>
                    Usa tu cuenta de Medifarma para acceder de forma segura al sistema.
                </p>
            </div>

            <div class="footer">
                &copy; {{ date('Y') }} Medifarma S.A. - Todos los derechos reservados
            </div>
        </div>
    </div>
</body>
</html>
