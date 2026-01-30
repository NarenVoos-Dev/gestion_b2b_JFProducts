{{-- Reporte de Ventas por Período --}}
<div class="space-y-6">
    {{-- Resumen Analítico --}}
    @php
        $analytics = $this->getPeriodAnalytics();
    @endphp
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <h3 class="text-sm font-medium text-gray-500">Total Ventas</h3>
            <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">${{ number_format($analytics['total_sales'], 2) }}</p>
        </div>
        <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <h3 class="text-sm font-medium text-gray-500">Total Pedidos</h3>
            <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $analytics['total_orders'] }}</p>
        </div>
        <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <h3 class="text-sm font-medium text-gray-500">Promedio por Pedido</h3>
            <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">${{ number_format($analytics['avg_order_value'], 2) }}</p>
        </div>
    </div>

    {{-- Top 10 Productos --}}
    <div class="rounded-lg bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        <div class="px-4 py-3 border-b border-gray-200 dark:border-white/5">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Top 10 Productos Más Vendidos</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full table-auto divide-y divide-gray-200 dark:divide-white/5">
                <thead class="bg-gray-50 dark:bg-white/5">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Producto</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Cantidad</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-white/5">
                    @foreach($analytics['top_products'] as $index => $product)
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $index + 1 }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">{{ $product['name'] }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-900 dark:text-white">{{ number_format($product['quantity'], 2) }}</td>
                            <td class="px-4 py-3 text-sm text-right font-medium text-gray-900 dark:text-white">${{ number_format($product['total'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Top 10 Clientes --}}
    <div class="rounded-lg bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        <div class="px-4 py-3 border-b border-gray-200 dark:border-white/5">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Top 10 Clientes que Más Compraron</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full table-auto divide-y divide-gray-200 dark:divide-white/5">
                <thead class="bg-gray-50 dark:bg-white/5">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Pedidos</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-white/5">
                    @foreach($analytics['top_clients'] as $index => $client)
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $index + 1 }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">{{ $client['name'] }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-900 dark:text-white">{{ $client['orders'] }}</td>
                            <td class="px-4 py-3 text-sm text-right font-medium text-gray-900 dark:text-white">${{ number_format($client['total'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    
    {{-- Botones de Exportación --}}
    @if($this->getSalesByPeriodData()->count() > 0)
        <div class="rounded-lg bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-4">
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-700 dark:text-gray-300">
                    Total de registros: <strong>{{ $this->getSalesByPeriodData()->count() }}</strong>
                </span>
                <div class="flex gap-2">
                    <x-filament::button wire:click="openExportModal('by_period')" color="success" size="sm" icon="heroicon-o-document-arrow-down">
                        Exportar Excel
                    </x-filament::button>


                </div>
            </div>
        </div>
    @endif
</div>
