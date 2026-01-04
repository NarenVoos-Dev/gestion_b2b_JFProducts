<!-- Métodos de Pago Disponibles -->
@if($paymentMethods->count() > 0)
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
    <h3 class="text-lg font-bold text-gray-800 mb-4">Métodos de Pago Disponibles</h3>
    <p class="text-sm text-gray-600 mb-6">Puedes realizar tus pagos a través de los siguientes canales:</p>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($paymentMethods as $method)
            <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 hover:shadow-md transition-all duration-200">
                <!-- Tipo de Método -->
                <div class="mb-3">
                    <span class="inline-block px-3 py-1 text-xs font-semibold rounded-full
                        @if($method->type === 'bank_account') bg-green-100 text-green-700
                        @elseif($method->type === 'qr_code') bg-blue-100 text-blue-700
                        @elseif($method->type === 'payment_link') bg-purple-100 text-purple-700
                        @else bg-gray-100 text-gray-700
                        @endif">
                        {{ $method->getTypeLabel() }}
                    </span>
                </div>

                <!-- Información Bancaria -->
                @if($method->type === 'bank_account')
                    <div class="space-y-2">
                        <div>
                            <p class="text-xs text-gray-500">Banco</p>
                            <p class="text-sm font-semibold text-gray-800">{{ $method->bank_name }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Tipo de Cuenta</p>
                            <p class="text-sm font-medium text-gray-700">{{ $method->getAccountTypeLabel() }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Número de Cuenta</p>
                            <div class="flex items-center gap-2">
                                <p class="text-sm font-mono font-semibold text-gray-800">{{ $method->account_number }}</p>
                                <button onclick="copyToClipboard('{{ $method->account_number }}')" 
                                        class="text-blue-600 hover:text-blue-800 transition" 
                                        title="Copiar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Titular</p>
                            <p class="text-sm font-medium text-gray-700">{{ $method->account_holder }}</p>
                        </div>
                    </div>
                @endif

                <!-- Código QR -->
                @if($method->type === 'qr_code' && $method->qr_code_image)
                    <div class="text-center">
                        <img src="{{ Storage::url($method->qr_code_image) }}" 
                             alt="Código QR" 
                             class="w-48 h-48 mx-auto rounded-lg border border-gray-200">
                        <p class="text-xs text-gray-600 mt-2">Escanea para pagar</p>
                    </div>
                @endif

                <!-- Link de Pago -->
                @if($method->type === 'payment_link' && $method->payment_link)
                    <div>
                        <a href="{{ $method->payment_link }}" 
                           target="_blank" 
                           class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
                            Ir a Pagar
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                        </a>
                    </div>
                @endif

                <!-- Descripción Adicional -->
                @if($method->description)
                    <div class="mt-3 pt-3 border-t border-gray-200">
                        <p class="text-xs text-gray-600">{{ $method->description }}</p>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>
@endif
