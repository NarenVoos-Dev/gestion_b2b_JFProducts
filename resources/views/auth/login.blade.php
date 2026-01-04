<x-guest-layout>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        body { 
            font-family: 'Inter', sans-serif; 
            background: linear-gradient(135deg, #0f4db3 0%, #028dff 100%);
        }
        
        .brand-gradient {
            background: linear-gradient(135deg, #0f4db3 0%, #028dff 100%);
            position: relative;
        }
        
        .floating-animation {
            animation: float 6s ease-in-out infinite;
        }
        
        .floating-animation-2 {
            animation: float-diagonal 8s ease-in-out infinite;
        }
        
        .floating-animation-3 {
            animation: float-rotate 10s ease-in-out infinite;
        }
        
        .floating-animation-4 {
            animation: pulse-float 7s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { 
                transform: translateY(0px) translateX(0px);
            }
            50% { 
                transform: translateY(-30px) translateX(10px);
            }
        }
        
        @keyframes float-diagonal {
            0%, 100% { 
                transform: translate(0, 0) scale(1);
            }
            25% { 
                transform: translate(20px, -20px) scale(1.1);
            }
            50% { 
                transform: translate(0, -40px) scale(0.9);
            }
            75% { 
                transform: translate(-20px, -20px) scale(1.1);
            }
        }
        
        @keyframes float-rotate {
            0%, 100% { 
                transform: translateY(0px) rotate(0deg);
            }
            33% { 
                transform: translateY(-25px) rotate(120deg);
            }
            66% { 
                transform: translateY(-15px) rotate(240deg);
            }
        }
        
        @keyframes pulse-float {
            0%, 100% { 
                transform: translateY(0px) scale(1);
                opacity: 0.1;
            }
            50% { 
                transform: translateY(-35px) scale(1.3);
                opacity: 0.25;
            }
        }
        
        .pulse-glow {
            animation: pulse-glow 3s infinite;
        }
        
        @keyframes pulse-glow {
            0%, 100% { 
                box-shadow: 0 0 30px rgba(255, 255, 255, 0.3),
                           0 0 60px rgba(15, 77, 179, 0.2);
            }
            50% { 
                box-shadow: 0 0 40px rgba(255, 255, 255, 0.5),
                           0 0 80px rgba(15, 77, 179, 0.4);
            }
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .input-group {
            position: relative;
            transition: all 0.3s ease;
        }
        
        .input-group:focus-within {
            transform: translateY(-2px);
        }
        
        .input-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #9CA3AF;
            transition: color 0.3s ease;
        }
        
        .input-focus:focus + .input-icon,
        .input-group:focus-within .input-icon {
            color: #0f4db3;
        }
        
        .input-focus {
            transition: all 0.3s ease;
        }
        
        .input-focus:focus {
            border-color: #0f4db3;
            box-shadow: 0 0 0 4px rgba(15, 77, 179, 0.1);
            outline: none;
        }
        
        .btn-primary-custom {
            background: linear-gradient(135deg, #0f4db3 0%, #028dff 100%);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn-primary-custom::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.5s ease;
        }
        
        .btn-primary-custom:hover::before {
            left: 100%;
        }
        
        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(15, 77, 179, 0.4);
        }
        
        .btn-primary-custom:active {
            transform: translateY(0);
        }
        
        .feature-badge {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
        }
        
        .feature-badge:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateX(5px);
        }
        
        .spinner {
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top: 3px solid white;
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .fade-in {
            animation: fadeIn 0.6s ease-out;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>

    <div class="min-h-screen flex overflow-hidden">
        <!-- Columna Izquierda: Branding -->
        <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden brand-gradient">
            <!-- Elementos decorativos flotantes deshabilitados para un diseño más limpio -->
            
            <div class="relative z-10 flex flex-col justify-center items-center w-full h-full px-12 py-8 text-white">
                <div class="flex flex-col items-center justify-center max-w-md mx-auto">
                    <!-- Logo Principal -->
                    <div class="mb-6 pulse-glow rounded-2xl p-4 bg-white bg-opacity-10 backdrop-filter backdrop-blur-lg">
                        <img src="{{ asset('img/logoNavbar.jpg') }}" alt="Logo JF Products" class="rounded-xl object-cover" style="width: 180px; height: 70px;">
                    </div>
                    
                    <!-- Contenido Principal -->
                    <div class="text-center w-full fade-in">
                        <h1 class="text-3xl font-bold mb-3 leading-tight">
                            Portal B2B
                            <span class="block text-xl font-medium text-blue-200 mt-2">JF Products SAS</span>
                        </h1>
                        
                        <p class="text-base mb-6 text-blue-100 leading-relaxed">
                            Tu plataforma integral para gestionar pedidos institucionales con tecnología de vanguardia
                        </p>
                        
                        <!-- Características destacadas -->
                        <div class="space-y-3 mb-6">
                            <div class="feature-badge flex items-center justify-center space-x-3 p-3 rounded-xl">
                                <div class="flex-shrink-0">
                                    <svg class="w-5 h-5 text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <span class="text-sm font-medium">Inventario en tiempo real 24/7</span>
                            </div>
                            <div class="feature-badge flex items-center justify-center space-x-3 p-3 rounded-xl">
                                <div class="flex-shrink-0">
                                    <svg class="w-5 h-5 text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                </div>
                                <span class="text-sm font-medium">Procesamiento rápido de pedidos</span>
                            </div>
                            <div class="feature-badge flex items-center justify-center space-x-3 p-3 rounded-xl">
                                <div class="flex-shrink-0">
                                    <svg class="w-5 h-5 text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                </div>
                                <span class="text-sm font-medium">Plataforma segura y confiable</span>
                            </div>
                        </div>
                        
                        <div class="bg-white bg-opacity-10 backdrop-filter backdrop-blur-lg rounded-xl p-3 border border-white border-opacity-20">
                            <p class="text-xs italic text-blue-100">
                                "Distribución de insumos médicos con los más altos estándares de calidad y servicio"
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Formulario de Login -->
        <div class="flex-1 flex flex-col justify-center py-4 px-4 sm:px-6 lg:px-12 xl:px-16 bg-gradient-to-br from-gray-50 to-gray-100">
            <div class="mx-auto w-full max-w-md">
                <!-- Card del formulario -->
                <div class="glass-card rounded-2xl shadow-2xl py-10 px-8 fade-in">
                    <!-- Header del formulario -->
                    <div class="text-center mb-8">
                        <!-- Logo móvil -->
                        <div class="lg:hidden mb-4">
                            <img src="{{ asset('img/logoNavbar.jpg') }}" alt="Logo JF Products" class="mx-auto rounded-xl object-cover shadow-lg" style="width: 130px; height: 50px;">
                        </div>
                        
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">¡Bienvenido de nuevo!</h2>
                        <p class="text-sm text-gray-600 mb-1">Ingresa a tu cuenta institucional</p>
                        <p class="text-xs text-gray-500">¿Necesitas acceso? <a href="#" class="text-blue-600 hover:text-blue-700 font-medium">Contáctanos</a></p>
                    </div>

                    <!-- Validación de errores -->
                    <x-validation-errors class="mb-4" />

                    @if (session('status'))
                        <div class="mb-4 p-3 rounded-lg text-sm text-center bg-green-50 border border-green-200 text-green-800">
                            {{ session('status') }}
                        </div>
                    @endif
                    
                    <!-- Formulario -->
                    <form method="POST" action="{{ route('login') }}" class="space-y-4" id="loginForm">
                        @csrf
                        
                        <!-- Campo Email -->
                        <div class="input-group">
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                                Correo Electrónico
                            </label>
                            <div class="relative">
                                <svg class="input-icon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                                </svg>
                                <input 
                                    id="email" 
                                    name="email" 
                                    type="email" 
                                    required 
                                    autofocus 
                                    autocomplete="username" 
                                    value="{{ old('email') }}"
                                    class="input-focus block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-xl placeholder-gray-400 transition duration-200 text-sm"
                                    placeholder="tu@empresa.com"
                                >
                            </div>
                        </div>

                        <!-- Campo Contraseña -->
                        <div class="input-group mt-4">
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                                Contraseña
                            </label>
                            <div class="relative">
                                <svg class="input-icon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                                <input 
                                    id="password" 
                                    name="password" 
                                    type="password" 
                                    required 
                                    autocomplete="current-password"
                                    class="input-focus block w-full pl-10 pr-10 py-2.5 border border-gray-300 rounded-xl placeholder-gray-400 transition duration-200 text-sm"
                                    placeholder="••••••••"
                                >
                                <button 
                                    type="button" 
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center"
                                    onclick="togglePassword()"
                                >
                                    <svg id="eye-icon" class="h-5 w-5 text-gray-400 hover:text-gray-600 cursor-pointer transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Recordar sesión y recuperar contraseña -->
                        <div class="flex items-center justify-between text-sm mt-6">
                            <div class="flex items-center">
                                <input 
                                    id="remember-me" 
                                    name="remember" 
                                    type="checkbox" 
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                >
                                <label for="remember-me" class="ml-2 block text-sm text-gray-700">
                                    Recordar sesión
                                </label>
                            </div>
                            @if (Route::has('password.request'))
                                <div class="text-sm">
                                    <a href="{{ route('password.request') }}" class="font-medium text-blue-600 hover:text-blue-700 transition duration-200">
                                        ¿Olvidaste tu contraseña?
                                    </a>
                                </div>
                            @endif
                        </div>

                        <!-- Botón de ingreso -->
                        <div class="mt-6">
                            <button 
                                type="submit" 
                                class="btn-primary-custom w-full flex justify-center items-center py-2.5 px-4 border border-transparent rounded-xl shadow-lg text-sm font-semibold text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                id="submitBtn"
                            >
                                <span id="btnText">Ingresar al Portal</span>
                                <span id="btnSpinner" class="spinner hidden ml-2"></span>
                            </button>
                        </div>
                        
                        <div class="text-center">
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="text-sm font-medium text-blue-600 hover:text-blue-700 transition duration-200">
                                    ¿Primera vez? Registra tu empresa aquí
                                </a>
                            @endif
                        </div>
                    </form>

                    <!-- Footer -->
                    <div class="mt-8 pt-6 border-t border-gray-200 text-center">
                        <p class="text-xs text-gray-500">
                            © 2024 JF Products SAS
                        </p>
                        <p class="text-xs text-gray-400 mt-1">
                            Conexión segura SSL
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"/>
                `;
            } else {
                passwordInput.type = 'password';
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                `;
            }
        }

        // Animación del botón de envío
        document.getElementById('loginForm').addEventListener('submit', function() {
            const btn = document.getElementById('submitBtn');
            const btnText = document.getElementById('btnText');
            const btnSpinner = document.getElementById('btnSpinner');
            
            btn.disabled = true;
            btnText.textContent = 'Ingresando...';
            btnSpinner.classList.remove('hidden');
        });
    </script>
</x-guest-layout>
