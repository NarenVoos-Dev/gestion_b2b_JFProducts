<!-- REEMPLAZAR DESDE LÍNEA 577 HASTA LÍNEA 644 en cuentas-pagar.blade.php -->
<!-- Content -->
<div id="modalContent" class="overflow-y-auto bg-gray-50" style="max-height: calc(85vh - 50px);">
    <div class="p-4 space-y-3">
        @if($paymentMethods->count() > 0)
            @foreach($paymentMethods as $method)
                <!-- Transferencia Bancaria -->
                @if($method->type === 'bank_account')
                    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                        <div class="bg-gradient-to-r from-blue-50 to-blue-100 px-3 py-2 border-b border-blue-200">
                            <h4 class="text-sm font-semibold text-gray-900">Transferencia Bancaria</h4>
                        </div>
                        <div class="p-3">
                            <table class="w-full text-xs">
                                <tbody class="divide-y divide-gray-100">
                                    <tr>
                                        <td class="py-2 text-gray-600 font-medium">Banco</td>
                                        <td class="py-2 text-right text-gray-900 font-semibold">{{ $method->bank_name }}</td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 text-gray-600 font-medium">Tipo de Cuenta</td>
                                        <td class="py-2 text-right text-gray-900 font-semibold">{{ $method->getAccountTypeLabel() }}</td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 text-gray-600 font-medium">Número</td>
                                        <td class="py-2 text-right text-gray-900 font-mono font-semibold">{{ $method->account_number }}</td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 text-gray-600 font-medium">Titular</td>
                                        <td class="py-2 text-right text-gray-900 font-semibold">{{ $method->account_holder }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                <!-- PSE -->
                @if($method->type === 'payment_link' && $method->payment_link)
                    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                        <div class="bg-gradient-to-r from-emerald-50 to-emerald-100 px-3 py-2 border-b border-emerald-200">
                            <h4 class="text-sm font-semibold text-gray-900">Pago Electrónico PSE</h4>
                        </div>
                        <div class="p-3">
                            <p class="text-xs text-gray-600 mb-2 leading-relaxed">
                                Realice su pago de forma segura directamente desde su entidad bancaria
                            </p>
                            <a href="{{ $method->payment_link }}" target="_blank" class="w-full bg-emerald-600 text-white font-medium py-2 px-3 rounded-md hover:bg-emerald-700 transition-colors duration-200 flex items-center justify-center gap-2 text-xs">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                Pagar con PSE
                            </a>
                            @if($method->description)
                                <p class="text-xs text-gray-500 text-center mt-2">{{ $method->description }}</p>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- QR -->
                @if($method->type === 'qr_code' && $method->qr_code_image)
                    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                        <div class="bg-gradient-to-r from-purple-50 to-purple-100 px-3 py-2 border-b border-purple-200">
                            <h4 class="text-sm font-semibold text-gray-900">Código QR</h4>
                        </div>
                        <div class="p-3">
                            <div class="bg-gray-50 rounded-lg p-3 mb-2 flex justify-center border border-gray-200">
                                <img src="{{ Storage::url($method->qr_code_image) }}" alt="Código QR" class="w-28 h-28 rounded-md">
                            </div>
                            <p class="text-xs text-gray-600 text-center leading-relaxed">Escanee el código con la aplicación de su banco</p>
                        </div>
                    </div>
                @endif
            @endforeach
        @else
            <div class="bg-white rounded-lg border border-gray-200 p-6 text-center">
                <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm text-gray-600">No hay métodos de pago configurados</p>
                <p class="text-xs text-gray-500 mt-2">Contacte al administrador para configurar los métodos de pago</p>
            </div>
        @endif
    </div>
