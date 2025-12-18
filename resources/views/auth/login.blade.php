<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Neo ERP</title>

    <!-- Favicons -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <meta name="theme-color" content="#3498db">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 50%, #2c3e50 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        /* Animated background shapes */
        body::before,
        body::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            background: rgba(52, 152, 219, 0.1);
            animation: float 20s infinite ease-in-out;
        }

        body::before {
            width: 600px;
            height: 600px;
            top: -300px;
            right: -200px;
            animation-delay: 0s;
        }

        body::after {
            width: 400px;
            height: 400px;
            bottom: -200px;
            left: -100px;
            animation-delay: 5s;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
        }

        .login-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 450px;
            padding: 20px;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 50px 40px;
            animation: slideIn 0.5s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo-container {
            text-align: center;
            margin-bottom: 35px;
        }

        .logo-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
            box-shadow: 0 10px 25px rgba(52, 152, 219, 0.3);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .logo-icon i {
            font-size: 2.5rem;
            color: white;
        }

        .brand-name {
            font-size: 2rem;
            font-weight: 700;
            color: #2c3e50;
            margin: 0;
            letter-spacing: 1px;
        }

        .brand-tagline {
            color: #7f8c8d;
            font-size: 0.9rem;
            margin-top: 5px;
        }

        .form-label {
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }

        .input-group {
            margin-bottom: 20px;
        }

        .input-group-text {
            background: #ecf0f1;
            border: 2px solid #ecf0f1;
            color: #7f8c8d;
            border-radius: 10px 0 0 10px;
            padding: 12px 15px;
        }

        .form-control {
            border: 2px solid #ecf0f1;
            border-left: none;
            border-radius: 0 10px 10px 0;
            padding: 12px 15px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .input-group.has-toggle .form-control {
            border-right: none;
            border-radius: 0;
        }

        .form-control:focus {
            border-color: #3498db;
            box-shadow: none;
            background: #fff;
        }

        .form-control:focus + .input-group-text,
        .input-group:focus-within .input-group-text {
            border-color: #3498db;
            background: #e3f2fd;
            color: #3498db;
        }

        .toggle-password {
            background: #ecf0f1;
            border: 2px solid #ecf0f1;
            border-left: none;
            border-radius: 0 10px 10px 0;
            padding: 12px 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            color: #7f8c8d;
        }

        .toggle-password:hover {
            color: #3498db;
        }

        .input-group:focus-within .toggle-password {
            border-color: #3498db;
            background: #e3f2fd;
            color: #3498db;
        }

        .form-check {
            margin-bottom: 25px;
        }

        .form-check-input:checked {
            background-color: #3498db;
            border-color: #3498db;
        }

        .form-check-label {
            color: #7f8c8d;
            font-size: 0.9rem;
        }

        .btn-login {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            border: none;
            border-radius: 10px;
            padding: 14px;
            font-size: 1rem;
            font-weight: 600;
            color: white;
            width: 100%;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
        }

        .btn-login:hover {
            background: linear-gradient(135deg, #2980b9 0%, #21618c 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(52, 152, 219, 0.4);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .alert {
            border-radius: 10px;
            border: none;
            margin-bottom: 25px;
            animation: shake 0.5s;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        .alert-danger {
            background: #fee;
            color: #c33;
            border-left: 4px solid #e74c3c;
        }

        .divider {
            text-align: center;
            margin: 25px 0;
            position: relative;
        }

        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #ecf0f1;
        }

        .divider span {
            background: rgba(255, 255, 255, 0.95);
            padding: 0 15px;
            color: #95a5a6;
            font-size: 0.85rem;
            position: relative;
        }

        .footer-text {
            text-align: center;
            margin-top: 30px;
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.85rem;
        }

        .footer-text a {
            color: #3498db;
            text-decoration: none;
            font-weight: 600;
        }

        .footer-text a:hover {
            text-decoration: underline;
        }

        /* Responsive */
        @media (max-width: 576px) {
            .login-card {
                padding: 35px 25px;
            }

            .brand-name {
                font-size: 1.75rem;
            }

            .logo-icon {
                width: 70px;
                height: 70px;
            }

            .logo-icon i {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="logo-container">
                <div class="logo-icon">
                    <i class="bi bi-box-seam"></i>
                </div>
                <h1 class="brand-name">Neo ERP</h1>
                <p class="brand-tagline">Sistema de Gestión Empresarial</p>
            </div>

            @if($errors->any())
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-circle me-2"></i>
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="mb-3">
                    <label for="email" class="form-label">Correo Electrónico</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-envelope-fill"></i>
                        </span>
                        <input type="email"
                               class="form-control"
                               id="email"
                               name="email"
                               placeholder="usuario@empresa.com"
                               value="{{ old('email') }}"
                               required
                               autofocus>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <div class="input-group has-toggle">
                        <span class="input-group-text">
                            <i class="bi bi-lock-fill"></i>
                        </span>
                        <input type="password"
                               class="form-control"
                               id="password"
                               name="password"
                               placeholder="••••••••"
                               required>
                        <span class="toggle-password" onclick="togglePassword()">
                            <i class="bi bi-eye-fill" id="toggleIcon"></i>
                        </span>
                    </div>
                </div>

                <div class="form-check">
                    <input type="checkbox"
                           class="form-check-input"
                           id="remember"
                           name="remember">
                    <label class="form-check-label" for="remember">
                        Mantener sesión iniciada
                    </label>
                </div>

                <button type="submit" class="btn btn-login">
                    <i class="bi bi-box-arrow-in-right me-2"></i>
                    Iniciar Sesión
                </button>
            </form>
        </div>

        <div class="footer-text">
            <p>&copy; {{ date('Y') }} Neo ERP. Todos los derechos reservados.</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('bi-eye-fill');
                toggleIcon.classList.add('bi-eye-slash-fill');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('bi-eye-slash-fill');
                toggleIcon.classList.add('bi-eye-fill');
            }
        }
    </script>
</body>
</html>
