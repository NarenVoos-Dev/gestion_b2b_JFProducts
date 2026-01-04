<x-guest-layout>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        body { 
            font-family: 'Inter', sans-serif; 
            background: #f8f9fa;
        }
        
        .input-focus:focus {
            border-color: #0f4db3;
            box-shadow: 0 0 0 3px rgba(15, 77, 179, 0.1);
            outline: none;
        }
        
        .btn-primary-custom {
            background: linear-gradient(135deg, #0f4db3 0%, #028dff 100%);
            transition: all 0.3s ease;
        }
        
        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(15, 77, 179, 0.4);
        }
    </style>

    <div class="min-h-screen flex items-center justify-center py-8 px-4">
        <div class="w-full max-w-5xl">
            <!-- Header compacto -->
            <div class="text-center mb-6">
                <h2 class="text-3xl font-bold text-gray-800 mb-1">Registro de Cliente</h2>
                <p class="text-sm text-gray-600">Completa el formulario para solicitar acceso al portal B2B</p>
            </div>

            <x-validation-errors class="mb-4" />

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <!-- Grid de 2 columnas -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    
                    <!-- Columna Izquierda -->
                    <div class="space-y-4">
                        <!-- Nombre de la Empresa -->
                        <div>
                            <label for="name" class="block text-xs font-medium text-gray-700 mb-1">
                                Nombre de la Empresa/Cliente *
                            </label>
                            <input 
                                id="name" 
                                type="text" 
                                name="name" 
                                value="{{ old('name') }}" 
                                required 
                                autofocus
                                class="input-focus block w-full px-3 py-2 bg-white border border-gray-300 rounded-lg placeholder-gray-400 transition duration-200 text-sm"
                                placeholder="Ej: Empresa XYZ S.A.S."
                            >
                        </div>

                        <!-- Tipo y Número de Documento -->
                        <div class="grid grid-cols-3 gap-2">
                            <div>
                                <label for="type_document" class="block text-xs font-medium text-gray-700 mb-1">
                                    Tipo *
                                </label>
                                <select 
                                    id="type_document" 
                                    name="type_document" 
                                    required
                                    class="input-focus block w-full px-2 py-2 bg-white border border-gray-300 rounded-lg transition duration-200 text-sm"
                                >
                                    <option value="NIT" {{ old('type_document') === 'NIT' ? 'selected' : '' }}>NIT</option>
                                    <option value="CC" {{ old('type_document') === 'CC' ? 'selected' : '' }}>CC</option>
                                    <option value="CE" {{ old('type_document') === 'CE' ? 'selected' : '' }}>CE</option>
                                </select>
                            </div>
                            <div class="col-span-2">
                                <label for="document" class="block text-xs font-medium text-gray-700 mb-1">
                                    Número de Documento *
                                </label>
                                <input 
                                    id="document" 
                                    type="text" 
                                    name="document" 
                                    value="{{ old('document') }}" 
                                    required
                                    class="input-focus block w-full px-3 py-2 bg-white border border-gray-300 rounded-lg placeholder-gray-400 transition duration-200 text-sm"
                                    placeholder="900123456-7"
                                >
                            </div>
                        </div>

                        <!-- Correo Electrónico -->
                        <div>
                            <label for="email" class="block text-xs font-medium text-gray-700 mb-1">
                                Correo Electrónico *
                            </label>
                            <input 
                                id="email" 
                                type="email" 
                                name="email" 
                                value="{{ old('email') }}" 
                                required
                                class="input-focus block w-full px-3 py-2 bg-white border border-gray-300 rounded-lg placeholder-gray-400 transition duration-200 text-sm"
                                placeholder="correo@empresa.com"
                            >
                        </div>

                        <!-- Teléfono -->
                        <div>
                            <label for="phone1" class="block text-xs font-medium text-gray-700 mb-1">
                                Teléfono de Contacto
                            </label>
                            <input 
                                id="phone1" 
                                type="tel" 
                                name="phone1" 
                                value="{{ old('phone1') }}"
                                class="input-focus block w-full px-3 py-2 bg-white border border-gray-300 rounded-lg placeholder-gray-400 transition duration-200 text-sm"
                                placeholder="3001234567"
                            >
                        </div>
                    </div>

                    <!-- Columna Derecha -->
                    <div class="space-y-4">
                        <!-- Contraseña -->
                        <div>
                            <label for="password" class="block text-xs font-medium text-gray-700 mb-1">
                                Contraseña *
                            </label>
                            <input 
                                id="password" 
                                type="password" 
                                name="password" 
                                required
                                class="input-focus block w-full px-3 py-2 bg-white border border-gray-300 rounded-lg placeholder-gray-400 transition duration-200 text-sm"
                                placeholder="Mínimo 8 caracteres"
                            >
                        </div>

                        <!-- Confirmar Contraseña -->
                        <div>
                            <label for="password_confirmation" class="block text-xs font-medium text-gray-700 mb-1">
                                Confirmar Contraseña *
                            </label>
                            <input 
                                id="password_confirmation" 
                                type="password" 
                                name="password_confirmation" 
                                required
                                class="input-focus block w-full px-3 py-2 bg-white border border-gray-300 rounded-lg placeholder-gray-400 transition duration-200 text-sm"
                                placeholder="Repite la contraseña"
                            >
                        </div>

                        <!-- Dirección -->
                        <div>
                            <label for="address" class="block text-xs font-medium text-gray-700 mb-1">
                                Dirección
                            </label>
                            <input 
                                id="address" 
                                type="text" 
                                name="address" 
                                value="{{ old('address') }}"
                                class="input-focus block w-full px-3 py-2 bg-white border border-gray-300 rounded-lg placeholder-gray-400 transition duration-200 text-sm"
                                placeholder="Calle 123 #45-67"
                            >
                        </div>

                        @if (Laravel\Jetstream\Jetstream::hasTermsAndPrivacyPolicyFeature())
                            <!-- Términos -->
                            <div class="flex items-start pt-2">
                                <div class="flex items-center h-5">
                                    <input 
                                        id="terms" 
                                        name="terms" 
                                        type="checkbox" 
                                        required
                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                    >
                                </div>
                                <div class="ml-2 text-xs">
                                    <label for="terms" class="text-gray-700">
                                        Acepto los 
                                        <a target="_blank" href="{{ route('terms.show') }}" class="text-blue-600 hover:text-blue-800 underline">términos</a>
                                        y 
                                        <a target="_blank" href="{{ route('policy.show') }}" class="text-blue-600 hover:text-blue-800 underline">política de privacidad</a>
                                    </label>
                                </div>
                            </div>
                        @endif

                        <!-- Nota informativa -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                            <p class="text-xs text-gray-700">
                                📋 Tu solicitud será revisada. Te notificaremos por correo cuando sea aprobada.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Botones -->
                <div class="flex items-center justify-between mt-6 pt-4 border-t border-gray-200">
                    <a href="{{ route('login') }}" class="text-sm text-gray-600 hover:text-gray-800 transition duration-200">
                        ← Volver al inicio de sesión
                    </a>

                    <button 
                        type="submit" 
                        class="btn-primary-custom flex justify-center items-center py-2.5 px-8 border border-transparent rounded-lg shadow-lg text-sm font-semibold text-white bg-white bg-opacity-20 backdrop-blur-sm hover:bg-opacity-30 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white"
                    >
                        Solicitar Acceso →
                    </button>
                </div>
            </form>

            <!-- Footer -->
            <div class="mt-6 text-center">
                <p class="text-xs text-gray-500">© 2024 JF Products SAS</p>
            </div>
        </div>
    </div>
</x-guest-layout>