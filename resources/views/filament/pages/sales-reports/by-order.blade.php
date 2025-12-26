{{-- Reporte de Ventas por Pedido --}}
<div class="rounded-lg bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
    <div class="overflow-x-auto">
        <table class="w-full table-auto divide-y divide-gray-200 dark:divide-white/5">
            <thead class="bg-gray-50 dark:bg-white/5">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pedido</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Precio</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-white/5">
                @forelse($this->getSalesByOrderData() as $sale)
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                            PW{{ str_pad($sale->sale_id, 5, '0', STR_PAD_LEFT) }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                            {{ \Carbon\Carbon::parse($sale->sale_date)->format('d/m/Y') }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                            {{ $sale->client_name }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                            {{ $sale->product_name }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-900 dark:text-white">
                            {{ number_format($sale->quantity, 2) }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-900 dark:text-white">
                            ${{ number_format($sale->price, 2) }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-900 dark:text-white">
                            ${{ number_format($sale->subtotal, 2) }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-right font-medium text-gray-900 dark:text-white">
                            ${{ number_format($sale->sale_total, 2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-sm text-gray-500">
                            No se encontraron ventas para los filtros seleccionados.
                        </td>
                    </tr>
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
                    <x-filament::button wire:click="exportToPDF('by_order')" color="danger" size="sm" icon="heroicon-o-document-arrow-down">
                        Exportar PDF
                    </x-filament::button>
                </div>
            </div>
        </div>
    @endif
</div>
