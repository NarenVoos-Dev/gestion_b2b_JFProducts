@extends('layouts.pos')

@section('title', 'Dashboard del Portal B2B')
@section('page-title', 'Dashboard')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        
        <!-- Welcome Block y Acceso Rápido -->
        <div class="bg-gradient-to-r from-[#0f4db3] to-[#028dff] rounded-xl p-5 text-white mb-6 shadow-2xl">
            <h2 class="text-3xl font-bold mb-2">¡Hola, {{ Auth::user()->name }}!</h2>
            <p class="text-gray-100 mb-1"> Tu portal de control para realizar pedidos, consultar el inventario y gestionar tus finanzas con JF Products SAS. </p>
            <p class="text-blue-100 mb-1">Aquí tienes un resumen completo de tu actividad comercial y métricas clave de rendimiento.</p>
        </div>
        
        <!-- Tarjetas de Navegación Rápida -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
            
            <!-- Tarjeta: Catálogo -->
            <a href="{{ route('catalogo') }}" class="bg-[#028dff] hover:bg-[#0f4db3] transition duration-300 rounded-xl p-5 text-white text-center shadow-lg transform hover:scale-[1.02]">
                <svg class="w-10 h-10 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M12 15h.01"/></svg>
                <span class="font-semibold text-lg">Catálogo y Pedidos</span>
                <p class="text-sm opacity-80">Inventario actualizado en tiempo real.</p>
            </a>

            <!-- Tarjeta: Pedidos -->
            <a href="{{ route('pedidos.list') }}" class="bg-[#0f4db3] hover:bg-[#0c3e90] transition duration-300 rounded-xl p-5 text-white text-center shadow-lg transform hover:scale-[1.02]">
                <svg class="w-10 h-10 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span class="font-semibold text-lg">Historial de Órdenes</span>
                <p class="text-sm opacity-80">Revisa el estado de tus pedidos.</p>
            </a>

            <!-- Tarjeta: Cuentas por Cobrar -->
            <a href="{{ route('cuentas.pagar') }}" class="bg-gray-700 hover:bg-gray-800 transition duration-300 rounded-xl p-5 text-white text-center shadow-lg transform hover:scale-[1.02]">
                <svg class="w-10 h-10 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                <span class="font-semibold text-lg">Cuentas por Pagar</span>
                <p class="text-sm opacity-80">Facturas, abonos y créditos.</p>
            </a>
        </div>


        <!-- KPI Cards (Métricas de Pedidos) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            
            <!-- Pedidos Pendientes -->
            <div class="bg-white rounded-lg p-4 shadow-md border border-gray-100 transform hover:scale-[1.02] transition duration-300">
                <div class="flex items-center justify-between">
                    <span class="text-xl font-bold text-yellow-600" id="kpi-pendientes">{{ $stats['total_pendientes'] }}</span>
                    <div class="bg-yellow-100 p-2 rounded-lg">
                        <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>
                <p class="text-xs font-medium text-gray-600 mt-2">Total Pedidos</p>
                <p class="font-semibold text-gray-800">Pendientes</p>
            </div>

            <!-- Pedidos Facturados -->
            <div class="bg-white rounded-lg p-4 shadow-md border border-gray-100 transform hover:scale-[1.02] transition duration-300">
                <div class="flex items-center justify-between">
                    <span class="text-xl font-bold text-[#028dff]" id="kpi-facturados">{{ $stats['total_proceso'] }}</span>
                    <div class="bg-blue-100 p-2 rounded-lg">
                        <svg class="w-5 h-5 text-[#028dff]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                </div>
                <p class="text-xs font-medium text-gray-600 mt-2">Total Pedidos</p>
                <p class="font-semibold text-gray-800">En Separación</p>
            </div>

            <!-- Pedidos Entregados -->
            <div class="bg-white rounded-lg p-4 shadow-md border border-gray-100 transform hover:scale-[1.02] transition duration-300">
                <div class="flex items-center justify-between">
                    <span class="text-xl font-bold text-green-600" id="kpi-entregados">{{ $stats['total_entregados'] }}</span>
                    <div class="bg-green-100 p-2 rounded-lg">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                </div>
                <p class="text-xs font-medium text-gray-600 mt-2">Total Pedidos</p>
                <p class="font-semibold text-gray-800">Finalizados</p>
            </div>

            <!-- Gasto Total 30 Días -->
            <div class="bg-white rounded-lg p-4 shadow-md border border-gray-100 transform hover:scale-[1.02] transition duration-300">
                <div class="flex items-center justify-between">
                    <span class="text-xl font-bold text-[#0f4db3]" id="kpi-gasto">${{ number_format($stats['gasto_total'], 0) }}</span>
                    <div class="bg-blue-100 p-2 rounded-lg">
                        <svg class="w-5 h-5 text-[#0f4db3]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path></svg>
                    </div>
                </div>
                <p class="text-xs font-medium text-gray-600 mt-2">Gasto Total</p>
                <p class="font-semibold text-gray-800">Todos los Pedidos</p>
            </div>
        </div>
        
        <!-- GRÁFICOS Y RESUMEN -->
        <div class="grid grid-cols-1 {{ $creditStats['credit_limit'] > 0 ? 'lg:grid-cols-3' : 'lg:grid-cols-1' }} gap-6">
            
            <!-- Performance Chart (2/3 de ancho o full si no hay crédito) -->
            <div class="{{ $creditStats['credit_limit'] > 0 ? 'lg:col-span-2' : 'lg:col-span-1' }} bg-white rounded-xl p-6 shadow-md border border-gray-100">
                <h3 class="text-lg font-semibold text-gray-800 mb-6">Valor Total de Pedidos - Últimos 6 Meses</h3>
                <div class="h-80">
                    <canvas id="performanceChart"></canvas>
                </div>
            </div>

            @if($creditStats['credit_limit'] > 0)

            <!-- Credit Card (1/3 de ancho) -->
            <div class="bg-white rounded-xl p-6 shadow-md border border-gray-100">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Crédito Disponible</h3>
                <div class="text-center">
                    <div class="bg-gradient-to-br from-[#0f4db3] to-[#028dff] rounded-full w-24 h-24 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                        </svg>
                    </div>
                    <p class="text-3xl font-bold text-[#0f4db3] mb-2">${{ number_format($creditStats['available_credit'], 0) }}</p>
                    <p class="text-sm text-gray-600 mb-4">Límite de crédito: ${{ number_format($creditStats['credit_limit'], 0) }}</p>
                    <div class="w-full bg-gray-200 rounded-full h-2 mb-4">
                        <div class="bg-gradient-to-r from-[#0f4db3] to-[#028dff] h-2 rounded-full" style="width: {{ 100 - $creditStats['credit_utilization_percentage'] }}%"></div>
                    </div>
                    <p class="text-xs text-gray-500">{{ round(100 - $creditStats['credit_utilization_percentage']) }}% disponible</p>
                </div>
                <div class="mt-6 pt-4 border-t border-gray-200">
                    <div class="flex justify-between text-sm mb-2">
                        <span class="text-gray-600">Utilizado:</span>
                        <span class="font-medium text-gray-800">${{ number_format($creditStats['current_debt'], 0) }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Próximo vencimiento:</span>
                        <span class="font-medium text-gray-800">{{ $nextDueDate ? \Carbon\Carbon::parse($nextDueDate)->format('d M') : 'N/A' }}</span>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Últimos Pedidos (Tabla debajo de los gráficos) -->
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 lg:p-8 mt-8">
            <h3 class="text-2xl font-bold text-gray-800 mb-4">Últimos Pedidos</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="text-left py-3 px-2 text-xs font-medium text-gray-600 uppercase tracking-wider">Pedido #</th>
                            <th class="text-left py-3 px-2 text-xs font-medium text-gray-600 uppercase tracking-wider">Fecha</th>
                            <th class="text-left py-3 px-2 text-xs font-medium text-gray-600 uppercase tracking-wider">Estado</th>
                            <th class="text-right py-3 px-2 text-xs font-medium text-gray-600 uppercase tracking-wider">Valor</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($latestOrders as $order)
                        <tr class="hover:bg-blue-50 transition duration-150 cursor-pointer">
                            <td class="py-3 px-2 text-sm font-medium text-gray-800">{{ $order->formatted_order_number }}</td>
                            <td class="py-3 px-2 text-sm text-gray-600">{{ $order->date->format('d M Y') }}</td>
                            <td class="py-3 px-2">
                                <span class="inline-flex px-2 py-1 text-xs font-medium {{ 
                                    $order->status === 'Entregado' ? 'bg-green-100 text-green-800' : 
                                    ($order->status === 'Pendiente' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-[#028dff]')
                                }} rounded-full">{{ $order->status }}</span>
                            </td>
                            <td class="py-3 px-2 text-sm font-medium text-gray-800 text-right">${{ number_format($order->total, 0) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-8 text-center text-gray-500">
                                No tienes pedidos aún. <a href="{{ route('catalogo') }}" class="text-[#028dff] hover:underline">Ir al catálogo</a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                <a href="{{ route('pedidos.list') }}" class="text-[#028dff] hover:text-[#0f4db3] text-sm font-medium">Ver todos los pedidos →</a>
            </div>
        </div>
        
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('performanceChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($chartData['labels']),
                datasets: [{
                    label: 'Valor Total de Pedidos',
                    data: @json($chartData['data']),
                    borderColor: '#028dff', // Azure Blue
                    backgroundColor: 'rgba(2, 141, 255, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#0f4db3', // Royal Blue
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        ticks: {
                            callback: function(value) {
                                // Formato a millones
                                return '$' + (value / 1000000).toFixed(1) + 'M';
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                },
                elements: {
                    point: {
                        hoverRadius: 8
                    }
                }
            }
        });
    });
</script>
@endpush
