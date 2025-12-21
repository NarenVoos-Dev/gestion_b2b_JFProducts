<div class="space-y-4">
    @if($payments->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Monto</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Método</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Ref.</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Comprobante</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Notas</th>
                        <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($payments as $payment)
                        <tr class="hover:bg-gray-50" id="payment-row-{{ $payment->id }}">
                            <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900">
                                {{ $payment->payment_date->format('d/m/Y') }}
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap text-sm font-semibold {{ $payment->amount > 0 ? 'text-green-600' : 'text-gray-400' }}">
                                ${{ number_format($payment->amount, 0) }}
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900">
                                {{ $payment->payment_method ?? '-' }}
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-500">
                                {{ $payment->reference ?? '-' }}
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap text-xs">
                                @if($payment->hasProof())
                                    <a href="{{ $payment->getProofUrl() }}" 
                                       target="_blank"
                                       class="inline-flex items-center px-2 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 transition">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        Ver
                                    </a>
                                @else
                                    <span class="text-gray-400 text-xs">-</span>
                                @endif
                            </td>
                            <td class="px-3 py-2 text-xs text-gray-500 max-w-xs truncate" title="{{ $payment->notes }}">
                                {{ $payment->notes ?? '-' }}
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap text-center">
                                @if($payment->amount == 0)
                                    <button 
                                        x-data="{ open: false }"
                                        @click="
                                            document.querySelectorAll('[id^=\'approve-form-\']').forEach(f => f.classList.add('hidden'));
                                            document.getElementById('approve-form-{{ $payment->id }}').classList.toggle('hidden');
                                        "
                                        style="background-color: #2563eb !important;"
                                        class="inline-flex items-center px-2 py-1 text-white rounded hover:opacity-80 transition text-xs font-medium">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Aprobar
                                    </button>
                                @else
                                    <span class="text-green-600 text-xs font-medium">✓</span>
                                @endif
                            </td>
                        </tr>
                        
                        @if($payment->amount == 0)
                        <tr id="approve-form-{{ $payment->id }}" class="hidden bg-blue-50">
                            <td colspan="7" class="px-4 py-4">
                                <form action="/admin/payments/{{ $payment->id }}/approve" method="POST" class="space-y-3">
                                    @csrf
                                    <div class="grid grid-cols-3 gap-4">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Monto del Pago *</label>
                                            <div class="relative">
                                                <span class="absolute left-2 top-1.5 text-gray-500 text-sm">$</span>
                                                <input type="number" name="amount" required min="1" step="0.01" max="{{ $account->balance }}"
                                                       class="block w-full pl-6 pr-2 py-1.5 text-sm border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500">
                                            </div>
                                            <p class="mt-0.5 text-xs text-gray-500">Máx: ${{ number_format($account->balance, 0) }}</p>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Método de Pago *</label>
                                            <select name="payment_method" required
                                                    class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500">
                                                <option value="Transferencia">Transferencia</option>
                                                <option value="Efectivo">Efectivo</option>
                                                <option value="Cheque">Cheque</option>
                                                <option value="Tarjeta">Tarjeta</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Referencia</label>
                                            <input type="text" name="reference" maxlength="100"
                                                   class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-end gap-2 pt-2">
                                        <button type="button" 
                                                @click="document.getElementById('approve-form-{{ $payment->id }}').classList.add('hidden')"
                                                class="px-3 py-1.5 text-xs bg-gray-200 text-gray-800 rounded hover:bg-gray-300">
                                            Cancelar
                                        </button>
                                        <button type="submit" 
                                                style="background-color: #16a34a !important;"
                                                class="px-3 py-1.5 text-xs text-white rounded hover:opacity-90">
                                            Aprobar Pago
                                        </button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="mt-4 p-3 bg-gray-50 rounded-lg">
            <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-gray-700">Total Pagado:</span>
                <span class="text-lg font-bold text-green-600">
                    ${{ number_format($payments->sum('amount'), 0) }}
                </span>
            </div>
        </div>
    @else
        <div class="text-center py-8 text-gray-500">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="mt-2">No hay pagos registrados</p>
        </div>
    @endif
</div>
