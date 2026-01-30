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
                <h1 class="text-3xl font-bold text-gray-800 mb-2">Política de Tratamiento de Datos Personales</h1>
                <p class="text-sm text-gray-600">JF Products S.A.S. - NIT: 901578103-1</p>
            </div>

            <!-- Contenido -->
            <div class="prose prose-sm max-w-none">
                <section class="mb-8">
                    <p class="text-gray-700 leading-relaxed mb-4">
                        De conformidad con la Ley 1581 de 2012 y el Decreto 1377 de 2013, JF Products S.A.S. informa 
                        a los usuarios sobre su política de tratamiento de datos personales.
                    </p>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        <strong>Última actualización:</strong> {{ date('d/m/Y') }}
                    </p>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-800 mb-4">Responsable del tratamiento</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        <strong>JF Products S.A.S.</strong><br>
                        NIT: 901578103-1<br>
                        Correo: administrativo@jfproductssas.com
                    </p>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-800 mb-4">Finalidad del tratamiento</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        Los datos personales serán utilizados para:
                    </p>
                    <ul class="list-disc pl-6 mb-4 text-gray-700 space-y-2">
                        <li>Gestión de cuenta y acceso a la plataforma</li>
                        <li>Procesamiento de pedidos y facturación</li>
                        <li>Comunicación sobre servicios y productos</li>
                        <li>Cumplimiento de obligaciones legales</li>
                    </ul>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-800 mb-4">Derechos del titular</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        Usted tiene derecho a conocer, actualizar, rectificar y suprimir sus datos personales, así como 
                        revocar la autorización otorgada para el tratamiento de los mismos.
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
