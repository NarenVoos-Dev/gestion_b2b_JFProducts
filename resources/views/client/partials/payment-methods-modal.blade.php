<!-- Modal de Información de Pago -->
<div id="paymentInfoModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full max-h-[90vh] overflow-y-auto">
        <!-- Header -->
        <div class="bg-blue-600 text-white px-6 py-4 rounded-t-lg flex items-center justify-between">
            <h3 class="text-lg font-bold">Información de Pago</h3>
            <button onclick="closePaymentModal()" class="text-white hover:text-gray-200 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Content -->
        <div class="p-6 space-y-4">
            @if($paymentMethods->count() > 0)
                @foreach($paymentMethods as $method)
                    <!-- Transferencia Bancaria -->
                    @if($method->type === 'bank_account')
                        <div class="bg-blue-50 rounded-lg p-4">
                            <h4 class="font-semibold text-gray-800 mb-3">Transferencia Bancaria</h4>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Banco</span>
                                    <span class="text-sm font-semibold text-gray-800">{{ $method->bank_name }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Tipo de Cuenta</span>
                                    <span class="text-sm font-semibold text-gray-800">{{ $method->getAccountTypeLabel() }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Número</span>
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-mono font-bold text-blue-600">{{ $method->account_number }}</span>
                                        <button onclick="copyToClipboard('{{ $method->account_number }}')" 
                                                class="text-blue-600 hover:text-blue-800 transition" 
                                                title="Copiar">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Titular</span>
                                    <span class="text-sm font-semibold text-gray-800">{{ $method->account_holder }}</span>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Pago Electrónico PSE -->
                    @if($method->type === 'payment_link' && $method->payment_link)
                        <div class="bg-green-50 rounded-lg p-4">
                            <h4 class="font-semibold text-gray-800 mb-3">Pago Electrónico PSE</h4>
                            <p class="text-sm text-gray-600 mb-3">Realice su pago de forma segura directamente desde su entidad bancaria</p>
                            <a href="{{ $method->payment_link }}" 
                               target="_blank" 
                               class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                Pagar con PSE
                            </a>
                            @if($method->description)
                                <p class="text-xs text-gray-500 mt-2">{{ $method->description }}</p>
                            @else
                                <p class="text-xs text-gray-500 mt-2">Próximamente disponible</p>
                            @endif
                        </div>
                    @endif

                    <!-- Código QR -->
                    @if($method->type === 'qr_code' && $method->qr_code_image)
                        <div class="bg-purple-50 rounded-lg p-4">
                            <h4 class="font-semibold text-gray-800 mb-3">Código QR</h4>
                            <div class="text-center">
                                <img src="{{ Storage::url($method->qr_code_image) }}" 
                                     alt="Código QR" 
                                     class="w-48 h-48 mx-auto rounded-lg border-2 border-purple-200">
                                <p class="text-sm text-gray-600 mt-3">Escanea este código para realizar el pago</p>
                            </div>
                        </div>
                    @endif

                    <!-- Efectivo -->
                    @if($method->type === 'cash')
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="font-semibold text-gray-800 mb-3">Pago en Efectivo</h4>
                            <p class="text-sm text-gray-600">{{ $method->description ?: 'Puede realizar el pago en nuestras oficinas' }}</p>
                        </div>
                    @endif
                @endforeach
            @else
                <div class="text-center py-8">
                    <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-gray-600">No hay métodos de pago configurados</p>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
function openPaymentModal() {
    document.getElementById('paymentInfoModal').classList.remove('hidden');
}

function closePaymentModal() {
    document.getElementById('paymentInfoModal').classList.add('hidden');
}

// Cerrar modal al hacer clic fuera
document.getElementById('paymentInfoModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closePaymentModal();
    }
});
</script>
