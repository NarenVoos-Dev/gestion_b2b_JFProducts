@extends('layouts.pos')

@section('title', 'Mi Perfil')
@section('page-title', 'Mi Perfil')

@section('content')
<!-- Mensajes de éxito/error -->
@if(session('success'))
<div id="successAlert" class="bg-green-50 border-l-4 border-green-400 p-4 mb-4 rounded-lg transition-opacity duration-500">
    <div class="flex">
        <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        <p class="ml-3 text-sm text-green-700 font-medium">{{ session('success') }}</p>
    </div>
</div>
<script>
    setTimeout(function() {
        const alert = document.getElementById('successAlert');
        if (alert) {
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.remove();
            }, 500);
        }
    }, 3000);
</script>
@endif

<x-validation-errors class="mb-4" />

<!-- Grid de 2 columnas -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    
    <!-- Columna Izquierda: Información de la Empresa (Solo Lectura) -->
    <div class="bg-white/95 backdrop-blur-xl rounded-xl p-6 shadow-lg border border-white/20">
        <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
            <svg class="w-6 h-6 mr-2 text-[#0f4db3]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            Información de la Empresa
        </h3>
        
        <div class="space-y-4">
            <!-- Nombre de la Empresa -->
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Nombre de la Empresa</label>
                <div class="px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-gray-700 font-medium">
                    {{ $client->name }}
                </div>
            </div>

            <!-- Tipo y Número de Documento -->
            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Tipo</label>
                    <div class="px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-gray-700 font-medium text-sm">
                        {{ $client->type_document }}
                    </div>
                </div>
                <div class="col-span-2">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Número de Documento</label>
                    <div class="px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-gray-700 font-medium">
                        {{ $client->document }}
                    </div>
                </div>
            </div>

            <!-- Correo Electrónico -->
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Correo Electrónico</label>
                <div class="px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-gray-700 font-medium">
                    {{ $client->email }}
                </div>
            </div>

            <!-- Nota informativa -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mt-4">
                <p class="text-xs text-blue-700">
                    🔒 Estos datos no pueden ser modificados. Si necesitas actualizarlos, contacta al administrador.
                </p>
            </div>
        </div>
    </div>

    <!-- Columna Derecha: Formulario Editable -->
    <div class="bg-white/95 backdrop-blur-xl rounded-xl p-6 shadow-lg border border-white/20">
        <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
            <svg class="w-6 h-6 mr-2 text-[#0f4db3]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Datos de Contacto
        </h3>

        <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
            @csrf
            @method('PUT')

            <!-- Teléfono Principal -->
            <div>
                <label for="phone1" class="block text-sm font-medium text-gray-700 mb-1">
                    Teléfono Principal *
                </label>
                <input 
                    type="tel" 
                    id="phone1" 
                    name="phone1" 
                    value="{{ old('phone1', $client->phone1) }}" 
                    required
                    class="block w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:border-[#0f4db3] focus:ring-2 focus:ring-[#0f4db3]/20 transition-all duration-300 outline-none text-sm"
                    placeholder="Ej: 3001234567"
                >
            </div>

            <!-- Teléfono Secundario -->
            <div>
                <label for="phone2" class="block text-sm font-medium text-gray-700 mb-1">
                    Teléfono Secundario
                </label>
                <input 
                    type="tel" 
                    id="phone2" 
                    name="phone2" 
                    value="{{ old('phone2', $client->phone2) }}"
                    class="block w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:border-[#0f4db3] focus:ring-2 focus:ring-[#0f4db3]/20 transition-all duration-300 outline-none text-sm"
                    placeholder="Ej: 3009876543"
                >
            </div>

            <!-- Dirección -->
            <div>
                <label for="address" class="block text-sm font-medium text-gray-700 mb-1">
                    Dirección
                </label>
                <input 
                    type="text" 
                    id="address" 
                    name="address" 
                    value="{{ old('address', $client->address) }}"
                    class="block w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:border-[#0f4db3] focus:ring-2 focus:ring-[#0f4db3]/20 transition-all duration-300 outline-none text-sm"
                    placeholder="Ej: Calle 123 #45-67"
                >
            </div>

            <!-- Separador -->
            <div class="border-t border-gray-200 my-6"></div>

            <h4 class="text-lg font-semibold text-gray-800 mb-3">Cambiar Contraseña</h4>
            <p class="text-xs text-gray-500 mb-4">Deja estos campos vacíos si no deseas cambiar tu contraseña</p>

            <!-- Nueva Contraseña -->
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                    Nueva Contraseña
                </label>
                <input 
                    type="password" 
                    id="password" 
                    name="password"
                    class="block w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:border-[#0f4db3] focus:ring-2 focus:ring-[#0f4db3]/20 transition-all duration-300 outline-none text-sm"
                    placeholder="Mínimo 8 caracteres"
                >
            </div>

            <!-- Confirmar Contraseña -->
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                    Confirmar Nueva Contraseña
                </label>
                <input 
                    type="password" 
                    id="password_confirmation" 
                    name="password_confirmation"
                    class="block w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:border-[#0f4db3] focus:ring-2 focus:ring-[#0f4db3]/20 transition-all duration-300 outline-none text-sm"
                    placeholder="Repite la contraseña"
                >
            </div>

            <!-- Botón Guardar -->
            <div class="pt-4">
                <button 
                    type="submit" 
                    class="w-full px-6 py-3 bg-gradient-to-r from-[#0f4db3] to-[#028dff] text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-300"
                >
                    💾 Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
