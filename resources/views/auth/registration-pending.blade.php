<x-guest-layout>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        body { 
            font-family: 'Inter', sans-serif; 
            background: linear-gradient(135deg, #0f4db3 0%, #028dff 100%);
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>

    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full">
            <div class="glass-card rounded-2xl shadow-2xl p-8 text-center">
                <!-- Icono de éxito -->
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-6">
                    <svg class="h-10 w-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>

                <!-- Logo -->
                <img src="{{ asset('img/logoNavbar.jpg') }}" alt="Logo JF Products" class="mx-auto rounded-xl object-cover shadow-lg mb-6" style="width: 120px; height: 50px;">

                <!-- Mensaje -->
                <h2 class="text-2xl font-bold text-gray-900 mb-4">¡Solicitud Enviada!</h2>
                <p class="text-gray-600 mb-6">
                    Tu solicitud de registro ha sido recibida exitosamente. Nuestro equipo revisará tu información y te notificaremos por correo electrónico una vez sea aprobada.
                </p>

                <!-- Información adicional -->
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6 text-left">
                    <h3 class="text-sm font-semibold text-blue-900 mb-2">¿Qué sigue?</h3>
                    <ul class="text-sm text-blue-700 space-y-2">
                        <li class="flex items-start">
                            <svg class="h-5 w-5 text-blue-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>Revisaremos tu información en las próximas 24-48 horas</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="h-5 w-5 text-blue-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>Recibirás un correo de confirmación cuando tu cuenta esté activa</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="h-5 w-5 text-blue-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>Podrás acceder al portal con las credenciales que registraste</span>
                        </li>
                    </ul>
                </div>

                <!-- Botón volver -->
                <a 
                    href="{{ route('login') }}" 
                    class="inline-flex items-center justify-center w-full py-3 px-4 border border-transparent rounded-xl shadow-lg text-sm font-semibold text-white bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-200"
                >
                    Volver al inicio de sesión
                </a>

                <!-- Contacto -->
                <p class="mt-6 text-xs text-gray-500">
                    ¿Tienes preguntas? Contáctanos a <a href="mailto:soporte@jfproducts.com" class="text-blue-600 hover:text-blue-700">soporte@jfproducts.com</a>
                </p>
            </div>
        </div>
    </div>
</x-guest-layout>
