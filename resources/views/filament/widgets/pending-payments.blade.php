<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Pagos Pendientes de Aprobación</span>
                @if($totalCount > 0)
                    <span class="ml-2 px-2 py-1 text-xs font-bold bg-yellow-100 text-yellow-800 rounded-full">
                        {{ $totalCount }}
                    </span>
                @endif
            </div>
        </x-slot>
        
        @if($totalCount > 0)
            <div class="space-y-3">
                @foreach($pendingPayments as $payment)
                    <div class="flex items-center justify-between p-4 bg-yellow-50 border border-yellow-200 rounded-lg hover:bg-yellow-100 transition">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <p class="font-semibold text-gray-900">
                                    {{ $payment->accountReceivable->client->name }}
                                </p>
                                <span class="px-2 py-0.5 text-xs bg-blue-100 text-blue-800 rounded">
                                    {{ $payment->accountReceivable->invoice_number }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-600">
                                {{ $payment->notes }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                Subido: {{ $payment->created_at->diffForHumans() }}
                            </p>
                        </div>
                        <div class="flex items-center gap-3 ml-4">
                            @if($payment->hasProof())
                                <a href="{{ $payment->getProofUrl() }}" 
                                   target="_blank"
                                   class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    Ver Comprobante →
                                </a>
                            @endif
                            <a href="{{ route('filament.admin.resources.account-receivables.pages.manage-payments', $payment->accountReceivable) }}" 
                               class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
                                Revisar y Aprobar
                            </a>
                        </div>
                    </div>
                @endforeach
                
                @if($totalCount > 10)
                    <div class="text-center pt-2">
                        <p class="text-sm text-gray-500">
                            Y {{ $totalCount - 10 }} pago(s) más pendiente(s)...
                        </p>
                        <a href="{{ route('filament.admin.resources.account-receivables.index') }}" 
                           class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                            Ver todos →
                        </a>
                    </div>
                @endif
            </div>
        @else
            <div class="text-center py-8">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-gray-500 font-medium">No hay pagos pendientes de aprobación</p>
                <p class="text-sm text-gray-400 mt-1">Todos los pagos han sido procesados</p>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
