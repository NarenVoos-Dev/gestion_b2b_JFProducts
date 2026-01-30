{{-- Reporte de Ventas por Pedido --}}
<div class="rounded-lg bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
    <div class="overflow-x-auto">
        <table class="w-full table-auto divide-y divide-gray-200 dark:divide-white/5">
            <thead class="bg-gray-50 dark:bg-white/5">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pedido</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ciudad</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Estado</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Fuente</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-white/5">
                @forelse($this->getSalesByOrderData() as $sale)
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">
                            #{{ $sale->invoice_number }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500">
                            {{ \Carbon\Carbon::parse($sale->date)->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                            <div class="font-medium">{{ $sale->client->name }}</div>
                            <div class="text-xs text-gray-500">{{ $sale->client->document }}</div>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500">
                            {{ $sale->client->city_name ?? 'N/A' }}
                        </td>
                        <td class="px-4 py-3 text-sm text-right font-bold text-gray-900 dark:text-white">
                            ${{ number_format($sale->total, 2) }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <x-filament::badge :color="match ($sale->status) {
                                'completed' => 'success',
                                'pending' => 'warning',
                                'cancelled' => 'danger',
                                default => 'gray',
                            }">
                                {{ ucfirst($sale->status) }}
                            </x-filament::badge>
                        </td>
                        <td class="px-4 py-3 text-center text-xs text-gray-500">
                            {{ ucfirst($sale->source ?? 'web') }}
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500">No hay datos para los filtros seleccionados</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($this->getSalesByOrderData()->count() > 0)
        <div class="px-4 py-3 bg-gray-50 dark:bg-white/5 border-t border-gray-200 dark:border-white/5">
            <div class="flex justify-between items-center text-sm">
                <span class="text-gray-700 dark:text-gray-300">
                    Total de registros: <strong>{{ $this->getSalesByOrderData()->count() }}</strong>
                </span>
                <div class="flex gap-2">
                    <x-filament::button wire:click="openExportModal('by_order')" color="success" size="sm" icon="heroicon-o-document-arrow-down">
                        Exportar Excel
                    </x-filament::button>


                </div>
            </div>
        </div>
    @endif
</div>
