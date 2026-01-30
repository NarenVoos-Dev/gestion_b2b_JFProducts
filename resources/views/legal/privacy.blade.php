<x-guest-layout>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        body { 
            font-family: 'Inter', sans-serif; 
            background: #f8f9fa;
        }
    </style>

    <div class="min-h-screen py-8 px-4">
        <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-lg p-8">
            <!-- Header -->
            <div class="mb-8 border-b pb-4">
                <h1 class="text-3xl font-bold text-gray-800 mb-2">Política de Privacidad</h1>
                <p class="text-sm text-gray-600">JF Products S.A.S. - NIT: 901578103-1</p>
            </div>

            <!-- Contenido -->
            <div class="prose prose-sm max-w-none">
                <section class="mb-8">
                    <p class="text-gray-700 leading-relaxed mb-4">
                        Esta política de privacidad describe cómo JFarma recopila, usa y protege su información personal 
                        cuando utiliza nuestro sitio web y servicios.
                    </p>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        <strong>Última actualización:</strong> {{ date('d/m/Y') }}
                    </p>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-800 mb-4">Información que recopilamos</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        Recopilamos información que usted nos proporciona directamente, como cuando crea una cuenta, realiza 
                        un pedido o se comunica con nosotros.
                    </p>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-800 mb-4">Uso de la información</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        Utilizamos su información para procesar pedidos, mejorar nuestros servicios y comunicarnos con usted 
                        sobre su cuenta y nuestros servicios.
                    </p>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-800 mb-4">Protección de datos</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        Implementamos medidas de seguridad técnicas y organizativas para proteger su información personal 
                        contra acceso no autorizado, alteración, divulgación o destrucción.
                    </p>
                </section>
            </div>

            <!-- Footer -->
            <div class="mt-8 pt-6 border-t">
                <div class="flex justify-between items-center">
                    <a href="{{ route('register') }}" class="text-blue-600 hover:text-blue-800 text-sm">
                        ← Volver al registro
                    </a>
                    <button onclick="window.close()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
