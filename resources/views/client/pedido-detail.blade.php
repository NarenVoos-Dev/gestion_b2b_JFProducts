@extends('layouts.pos')

@section('title', 'Detalle del Pedido #' . str_pad($sale->id, 6, '0', STR_PAD_LEFT))

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-8">
    <div class="container mx-auto px-4 max-w-5xl">
        <!-- Header -->
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Pedido #{{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }}</h1>
                <p class="text-gray-600">{{ $sale->date->format('d/m/Y h:i A') }}</p>
            </div>
            <a href="/pedidos" class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold rounded-xl transition-all">
                ← Volver a Pedidos
            </a>
        </div>

        <!-- Estado del Pedido -->
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-gray-900 mb-2">Estado del Pedido</h2>
                    @php
                        $statusColors = [
                            'Pendiente' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                            'Separación' => 'bg-blue-100 text-blue-800 border-blue-200',
                            'Facturado' => 'bg-green-100 text-green-800 border-green-200',
                            'Finalizado' => 'bg-gray-100 text-gray-800 border-gray-200',
                        ];
                        $colorClass = $statusColors[$sale->status] ?? 'bg-gray-100 text-gray-800';
                        $icon = $statusIcons[$sale->status] ?? '📋';
                    @endphp
                    <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-lg font-semibold border {{ $colorClass }}">
                        {{ $sale->status }}
                    </span>
                </div>
                
                @if($sale->notes)
                    <div class="text-right">
                        <p class="text-sm text-gray-600 mb-1">Notas:</p>
                        <p class="text-gray-900">{{ $sale->notes }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Items del Pedido -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden mb-6">
            <div class="px-6 py-4 bg-gradient-to-r from-gray-100 to-gray-200 border-b">
                <h2 class="text-xl font-bold text-gray-900">Productos</h2>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-bold text-gray-700">Producto</th>
                            <th class="px-6 py-3 text-left text-sm font-bold text-gray-700">Lote</th>
                            <th class="px-6 py-3 text-right text-sm font-bold text-gray-700">Cantidad</th>
                            <th class="px-6 py-3 text-right text-sm font-bold text-gray-700">Precio Unit.</th>
                            <th class="px-6 py-3 text-right text-sm font-bold text-gray-700">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($sale->items as $item)
                            <tr class="hover:bg-gray-50">
                                <!-- Producto -->
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-gray-900">{{ $item->product->name }}</div>
                                    <div class="text-sm text-gray-600">{{ $item->product->laboratory->name ?? 'N/A' }}</div>
                                </td>
                                
                                <!-- Lote -->
                                <td class="px-6 py-4">
                                    @if($item->lot_number)
                                        <div class="text-sm">
                                            <span class="inline-flex items-center gap-1 px-2 py-1 bg-blue-100 text-blue-800 rounded font-medium">
                                                {{ $item->lot_number }}
                                            </span>
                                        </div>
                                        @if($item->expiration_date)
                                            <div class="text-xs text-gray-500 mt-1">
                                                Vence: {{ $item->expiration_date->format('d/m/Y') }}
                                            </div>
                                        @endif
                                    @else
                                        <span class="text-xs text-amber-600 bg-amber-50 px-2 py-1 rounded">
                                            Sin lote asignado
                                        </span>
                                    @endif
                                </td>
                                
                                <!-- Cantidad -->
                                <td class="px-6 py-4 text-right">
                                    <span class="font-semibold text-gray-900">{{ $item->quantity }}</span>
                                </td>
                                
                                <!-- Precio Unitario -->
                                <td class="px-6 py-4 text-right">
                                    <span class="text-gray-900">${{ number_format($item->price, 0, ',', '.') }}</span>
                                </td>
                                
                                <!-- Subtotal -->
                                <td class="px-6 py-4 text-right">
                                    <span class="font-bold text-gray-900">${{ number_format($item->price * $item->quantity, 0, ',', '.') }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Totales -->
        <div class="bg-white rounded-2xl shadow-lg p-6">
            <div class="max-w-md ml-auto">
                <div class="flex justify-between text-gray-600 mb-3">
                    <span>Subtotal:</span>
                    <span class="font-semibold">${{ number_format($sale->subtotal, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-gray-600 mb-3">
                    <span>IVA:</span>
                    <span class="font-semibold">${{ number_format($sale->tax, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-2xl font-bold text-gray-900 pt-3 border-t">
                    <span>Total:</span>
                    <span class="text-blue-600">${{ number_format($sale->total, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
