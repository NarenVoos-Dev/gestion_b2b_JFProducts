<x-guest-layout>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        
        body { 
            font-family: 'Inter', sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .register-container {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        
        .input-focus:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            outline: none;
        }
        
        .input-wrapper {
            position: relative;
        }
        
        .input-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            pointer-events: none;
        }
        
        .input-wrapper input,
        .input-wrapper select {
            padding-left: 40px;
        }
        
        .section-title {
            position: relative;
            padding-left: 20px;
        }
        
        .section-title::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 2px;
        }
        
        .btn-primary-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
        }
        
        .btn-primary-custom:active {
            transform: translateY(0);
        }
        
        .info-card {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border-left: 4px solid #667eea;
        }
        
        .terms-card {
            background: #f9fafb;
            border: 2px solid #e5e7eb;
            transition: all 0.3s ease;
        }
        
        .terms-card:hover {
            border-color: #667eea;
            background: #f3f4f6;
        }
        
        .step-indicator {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-bottom: 2rem;
        }
        
        .step-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #d1d5db;
            transition: all 0.3s ease;
        }
        
        .step-dot.active {
            background: #667eea;
            width: 24px;
            border-radius: 4px;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .fade-in {
            animation: fadeIn 0.5s ease-out;
        }
    </style>

    <div class="min-h-screen flex items-center justify-center py-8 px-4">
        <div class="w-full max-w-6xl">
            <!-- Logo/Header Section -->
            <div class="text-center mb-8 fade-in">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-white rounded-full shadow-lg mb-4">
                    <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
                <h1 class="text-4xl font-bold text-white mb-2">Registro de Cliente B2B</h1>
                <p class="text-white/90 text-lg">Crea tu cuenta y accede al portal de pedidos</p>
            </div>

            <!-- Main Card -->
            <div class="register-container rounded-2xl p-8 md:p-10 fade-in">
                <x-validation-errors class="mb-6" />

                <form method="POST" action="{{ route('register') }}">
                    @csrf

                    <!-- Step Indicator -->
                    <div class="step-indicator">
                        <div class="step-dot active"></div>
                        <div class="step-dot"></div>
                        <div class="step-dot"></div>
                    </div>

                    <!-- Form Sections -->
                    <div class="space-y-8">
                        <!-- Sección 1: Información de la Empresa -->
                        <div>
                            <h2 class="section-title text-xl font-semibold text-gray-800 mb-6">
                                Información de la Empresa
                            </h2>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Nombre de la Empresa -->
                                <div class="md:col-span-2">
                                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                        Nombre de la Empresa/Cliente <span class="text-red-500">*</span>
                                    </label>
                                    <div class="input-wrapper">
                                        <svg class="input-icon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                        </svg>
                                        <input 
                                            id="name" 
                                            type="text" 
                                            name="name" 
                                            value="{{ old('name') }}" 
                                            required 
                                            autofocus
                                            class="input-focus block w-full px-4 py-3 bg-white border border-gray-300 rounded-xl placeholder-gray-400 transition duration-200 text-sm"
                                            placeholder="Ej: Empresa XYZ S.A.S."
                                        >
                                    </div>
                                </div>

                                <!-- Tipo de Documento -->
                                <div>
                                    <label for="type_document" class="block text-sm font-medium text-gray-700 mb-2">
                                        Tipo de Documento <span class="text-red-500">*</span>
                                    </label>
                                    <div class="input-wrapper">
                                        <svg class="input-icon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        <select 
                                            id="type_document" 
                                            name="type_document" 
                                            required
                                            class="input-focus block w-full px-4 py-3 bg-white border border-gray-300 rounded-xl transition duration-200 text-sm appearance-none"
                                        >
                                            <option value="NIT" {{ old('type_document') === 'NIT' ? 'selected' : '' }}>NIT</option>
                                            <option value="CC" {{ old('type_document') === 'CC' ? 'selected' : '' }}>Cédula de Ciudadanía</option>
                                            <option value="CE" {{ old('type_document') === 'CE' ? 'selected' : '' }}>Cédula de Extranjería</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Número de Documento -->
                                <div>
                                    <label for="document" class="block text-sm font-medium text-gray-700 mb-2">
                                        Número de Documento <span class="text-red-500">*</span>
                                    </label>
                                    <div class="input-wrapper">
                                        <svg class="input-icon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"></path>
                                        </svg>
                                        <input 
                                            id="document" 
                                            type="text" 
                                            name="document" 
                                            value="{{ old('document') }}" 
                                            required
                                            class="input-focus block w-full px-4 py-3 bg-white border border-gray-300 rounded-xl placeholder-gray-400 transition duration-200 text-sm"
                                            placeholder="900123456-7"
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sección 2: Información de Contacto -->
                        <div>
                            <h2 class="section-title text-xl font-semibold text-gray-800 mb-6">
                                Información de Contacto
                            </h2>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Correo Electrónico -->
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                        Correo Electrónico <span class="text-red-500">*</span>
                                    </label>
                                    <div class="input-wrapper">
                                        <svg class="input-icon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                        <input 
                                            id="email" 
                                            type="email" 
                                            name="email" 
                                            value="{{ old('email') }}" 
                                            required
                                            class="input-focus block w-full px-4 py-3 bg-white border border-gray-300 rounded-xl placeholder-gray-400 transition duration-200 text-sm"
                                            placeholder="correo@empresa.com"
                                        >
                                    </div>
                                </div>

                                <!-- Teléfono -->
                                <div>
                                    <label for="phone1" class="block text-sm font-medium text-gray-700 mb-2">
                                        Teléfono de Contacto
                                    </label>
                                    <div class="input-wrapper">
                                        <svg class="input-icon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                        </svg>
                                        <input 
                                            id="phone1" 
                                            type="tel" 
                                            name="phone1" 
                                            value="{{ old('phone1') }}"
                                            class="input-focus block w-full px-4 py-3 bg-white border border-gray-300 rounded-xl placeholder-gray-400 transition duration-200 text-sm"
                                            placeholder="3001234567"
                                        >
                                    </div>
                                </div>

                                <!-- Dirección -->
                                <div class="md:col-span-2">
                                    <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                                        Dirección
                                    </label>
                                    <div class="input-wrapper">
                                        <svg class="input-icon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        <input 
                                            id="address" 
                                            type="text" 
                                            name="address" 
                                            value="{{ old('address') }}"
                                            class="input-focus block w-full px-4 py-3 bg-white border border-gray-300 rounded-xl placeholder-gray-400 transition duration-200 text-sm"
                                            placeholder="Calle 123 #45-67, Ciudad"
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sección 3: Seguridad -->
                        <div>
                            <h2 class="section-title text-xl font-semibold text-gray-800 mb-6">
                                Seguridad de la Cuenta
                            </h2>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Contraseña -->
                                <div>
                                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                                        Contraseña <span class="text-red-500">*</span>
                                    </label>
                                    <div class="input-wrapper">
                                        <svg class="input-icon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                        </svg>
                                        <input 
                                            id="password" 
                                            type="password" 
                                            name="password" 
                                            required
                                            class="input-focus block w-full px-4 py-3 bg-white border border-gray-300 rounded-xl placeholder-gray-400 transition duration-200 text-sm"
                                            placeholder="Mínimo 8 caracteres"
                                        >
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500">Mínimo 8 caracteres</p>
                                </div>

                                <!-- Confirmar Contraseña -->
                                <div>
                                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                                        Confirmar Contraseña <span class="text-red-500">*</span>
                                    </label>
                                    <div class="input-wrapper">
                                        <svg class="input-icon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                        </svg>
                                        <input 
                                            id="password_confirmation" 
                                            type="password" 
                                            name="password_confirmation" 
                                            required
                                            class="input-focus block w-full px-4 py-3 bg-white border border-gray-300 rounded-xl placeholder-gray-400 transition duration-200 text-sm"
                                            placeholder="Repite la contraseña"
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sección 4: Términos y Condiciones -->
                        <div>
                            <h2 class="section-title text-xl font-semibold text-gray-800 mb-6">
                                Términos y Condiciones
                            </h2>
                            
                            <div class="terms-card rounded-xl p-6">
                                <div class="flex items-start">
                                    <div class="flex items-center h-5 mt-0.5">
                                        <input 
                                            id="terms" 
                                            name="terms" 
                                            type="checkbox" 
                                            required
                                            class="h-5 w-5 text-purple-600 focus:ring-purple-500 border-gray-300 rounded cursor-pointer"
                                            {{ old('terms') ? 'checked' : '' }}
                                        >
                                    </div>
                                    <div class="ml-4 flex-1">
                                        <label for="terms" class="text-sm text-gray-700 cursor-pointer">
                                            He leído y acepto los 
                                            <a target="_blank" href="{{ route('terms.show') }}" class="text-purple-600 hover:text-purple-800 underline font-medium transition-colors">Términos y Condiciones</a>, 
                                            la 
                                            <a target="_blank" href="{{ route('privacy.show') }}" class="text-purple-600 hover:text-purple-800 underline font-medium transition-colors">Política de Privacidad</a>
                                            y la 
                                            <a target="_blank" href="{{ route('data-treatment.show') }}" class="text-purple-600 hover:text-purple-800 underline font-medium transition-colors">Política de Tratamiento de Datos</a>
                                            de JFarma <span class="text-red-500">*</span>
                                        </label>
                                    </div>
                                </div>
                                @error('terms')
                                    <p class="mt-3 text-sm text-red-600 flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>

                        <!-- Nota Informativa -->
                        <div class="info-card rounded-xl p-5">
                            <div class="flex items-start">
                                <svg class="w-6 h-6 text-blue-600 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-800 mb-1">Proceso de Aprobación</h3>
                                    <p class="text-sm text-gray-700">
                                        Tu solicitud será revisada por nuestro equipo. Te notificaremos por correo electrónico cuando tu cuenta sea aprobada y puedas acceder al portal B2B.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="flex flex-col sm:flex-row items-center justify-between mt-8 pt-6 border-t border-gray-200 gap-4">
                        <a href="{{ route('login') }}" class="text-sm text-gray-600 hover:text-gray-800 transition duration-200 flex items-center group">
                            <svg class="w-4 h-4 mr-2 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                            Volver al inicio de sesión
                        </a>

                        <button 
                            type="submit" 
                            class="btn-primary-custom flex justify-center items-center py-3 px-10 rounded-xl text-sm font-semibold text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 w-full sm:w-auto"
                        >
                            <span>Crear Cuenta</span>
                            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                            </svg>
                        </button>
                    </div>
                </form>

                <!-- Footer -->
                <div class="mt-8 pt-6 border-t border-gray-200 text-center">
                    <p class="text-xs text-gray-500">
                        © {{ date('Y') }} JF Products SAS. Todos los derechos reservados.
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
