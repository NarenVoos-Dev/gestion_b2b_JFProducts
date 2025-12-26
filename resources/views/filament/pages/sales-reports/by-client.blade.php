{{-- Reporte de Ventas por Cliente --}}
<div class="rounded-lg bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
    <div class="overflow-x-auto">
        <table class="w-full table-auto divide-y divide-gray-200 dark:divide-white/5">
            <thead class="bg-gray-50 dark:bg-white/5">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Documento</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Pedidos</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total Comprado</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Promedio</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Última Compra</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-white/5">
                @forelse($this->getSalesByClientData() as $client)
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">{{ $client->client_name }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $client->document }}</td>
                        <td class="px-4 py-3 text-sm text-right text-gray-900 dark:text-white">{{ $client->total_orders }}</td>
                        <td class="px-4 py-3 text-sm text-right font-medium text-gray-900 dark:text-white">${{ number_format($client->total_purchased, 2) }}</td>
                        <td class="px-4 py-3 text-sm text-right text-gray-900 dark:text-white">${{ number_format($client->avg_order_value, 2) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ \Carbon\Carbon::parse($client->last_purchase_date)->format('d/m/Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">No hay datos</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($this->getSalesByClientData()->count() > 0)
        <div class="px-4 py-3 bg-gray-50 dark:bg-white/5 border-t border-gray-200 dark:border-white/5">
            <div class="flex justify-between items-center text-sm">
                <span class="text-gray-700 dark:text-gray-300">
                    Total de registros: <strong>{{ $this->getSalesByClientData()->count() }}</strong>
                </span>
                <div class="flex gap-2">
                    <x-filament::button wire:click="openExportModal('by_client')" color="success" size="sm" icon="heroicon-o-document-arrow-down">
                        Exportar Excel
                    </x-filament::button>
                    <x-filament::button wire:click="exportToPDF('by_client')" color="danger" size="sm" icon="heroicon-o-document-arrow-down">
                        Exportar PDF
                    </x-filament::button>
                </div>
            </div>
        </div>
    @endif
</div>
