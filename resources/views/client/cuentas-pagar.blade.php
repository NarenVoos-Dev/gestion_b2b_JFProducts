@extends('layouts.pos')

@section('title', 'Cuentas por Pagar')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Cuentas por Pagar</h1>
                <p class="text-gray-600 mt-1">Gestiona tus facturas pendientes y comprobantes de pago</p>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-500">Saldo Total</p>
                <p class="text-3xl font-bold text-red-600">${{ number_format($totalBalance, 2) }}</p>
                @if($paymentMethods->count() > 0)
                    <button onclick="openPaymentModal()" 
                            class="mt-3 inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Ver Métodos de Pago
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Mensajes de éxito/error -->
    @if(session('success'))
        <div id="successToast" class="fixed top-4 right-4 z-50 max-w-md bg-green-500 text-white px-6 py-4 rounded-lg shadow-2xl transform transition-all duration-300 ease-in-out">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="font-medium">{{ session('success') }}</p>
                </div>
                <button onclick="closeToast()" class="ml-4 text-white hover:text-gray-200 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div id="errorToast" class="fixed top-4 right-4 z-50 max-w-md bg-red-500 text-white px-6 py-4 rounded-lg shadow-2xl transform transition-all duration-300 ease-in-out">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="font-medium">{{ session('error') }}</p>
                </div>
                <button onclick="closeToast()" class="ml-4 text-white hover:text-gray-200 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    @endif

    <!-- Dashboard B2B - Diseño Médico Profesional -->
    <div class="space-y-6 mb-6">
        
        <!-- Tarjetas de Resumen Financiero -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Total Adeudado -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5 hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Total Adeudado</p>
                        <p class="text-2xl font-bold text-gray-900">
                            ${{ number_format($accounts->sum('balance'), 2) }}
                        </p>
                        @php
                            $totalFacturas = $accounts->count();
                        @endphp
                        <p class="text-xs text-gray-600 mt-2">{{ $totalFacturas }} factura{{ $totalFacturas != 1 ? 's' : '' }}</p>
                    </div>
                    <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Pagos Pendientes de Aprobación -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5 hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Pagos Pendientes</p>
                        @php
                            $pagosPendientes = \App\Models\AccountPayment::where('amount', 0)
                                ->whereHas('accountReceivable', function($q) {
                                    $q->where('client_id', auth()->user()->client_id);
                                })->count();
                        @endphp
                        <p class="text-2xl font-bold text-gray-900">{{ $pagosPendientes }}</p>
                        <p class="text-xs text-gray-600 mt-2">En revisión</p>
                    </div>
                    <div class="w-10 h-10 bg-amber-50 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Próximo a Vencer -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5 hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Próximo a Vencer</p>
                        @php
                            $proximoVencer = $accounts->filter(function($account) {
                                return $account->due_date && 
                                       $account->due_date->isFuture() && 
                                       $account->due_date->diffInDays(now()) <= 7 &&
                                       $account->balance > 0;
                            });
                            $montoProximo = $proximoVencer->sum('balance');
                        @endphp
                        <p class="text-2xl font-bold text-gray-900">
                            ${{ number_format($montoProximo, 2) }}
                        </p>
                        <p class="text-xs text-gray-600 mt-2">{{ $proximoVencer->count() }} factura(s)</p>
                    </div>
                    <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Facturas Vencidas -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5 hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Facturas Vencidas</p>
                        @php
                            $vencidas = $accounts->filter(function($account) {
                                return $account->due_date && 
                                       $account->due_date->isPast() &&
                                       $account->balance > 0;
                            });
                            $montoVencido = $vencidas->sum('balance');
                        @endphp
                        <p class="text-2xl font-bold text-gray-900">
                            ${{ number_format($montoVencido, 2) }}
                        </p>
                        <p class="text-xs text-gray-600 mt-2">{{ $vencidas->count() }} factura(s)</p>
                    </div>
                    <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>


        @include('client.partials.payment-methods-modal')

        <!-- Alertas y Accesos Rápidos -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            
            <!-- Alertas (2/3) -->
            <div class="lg:col-span-2 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-900 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        Alertas y Notificaciones
                    </h3>
                </div>
                <div class="space-y-3">
                    @if($proximoVencer->count() > 0)
                        <div class="flex items-start gap-3 p-4 bg-blue-50 border-l-4 border-blue-600 rounded-r">
                            <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-gray-900">Facturas por vencer</p>
                                <p class="text-xs text-gray-600 mt-1">
                                    Tienes {{ $proximoVencer->count() }} factura(s) que vencen en los próximos 7 días
                                </p>
                            </div>
                        </div>
                    @endif

                    @if($pagosPendientes > 0)
                        <div class="flex items-start gap-3 p-4 bg-amber-50 border-l-4 border-amber-500 rounded-r">
                            <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-gray-900">Pagos en revisión</p>
                                <p class="text-xs text-gray-600 mt-1">
                                    Tienes {{ $pagosPendientes }} pago(s) pendiente(s) de aprobación
                                </p>
                            </div>
                        </div>
                    @endif

                    @if($vencidas->count() > 0)
                        <div class="flex items-start gap-3 p-4 bg-gray-50 border-l-4 border-gray-600 rounded-r">
                            <svg class="w-5 h-5 text-gray-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-gray-900">Facturas vencidas</p>
                                <p class="text-xs text-gray-600 mt-1">
                                    Tienes {{ $vencidas->count() }} factura(s) vencida(s) por un total de ${{ number_format($montoVencido, 2) }}
                                </p>
                            </div>
                        </div>
                    @endif

                    @if($proximoVencer->count() == 0 && $pagosPendientes == 0 && $vencidas->count() == 0)
                        <div class="flex items-center justify-center py-12 text-gray-400">
                            <div class="text-center">
                                <svg class="w-16 h-16 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p class="text-sm font-medium text-gray-500">No tienes alertas pendientes</p>
                                <p class="text-xs text-gray-400 mt-1">Todo está al día</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Accesos Rápidos (1/3) -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-900 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        Accesos Rápidos
                    </h3>
                </div>
                <div class="space-y-2">
                    <button onclick="exportToExcel()" class="w-full flex items-center gap-3 p-3 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors border border-gray-200 group">
                        <div class="w-9 h-9 bg-emerald-600 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div class="flex-1 text-left">
                            <p class="text-sm font-medium text-gray-900">Exportar a Excel</p>
                            <p class="text-xs text-gray-500">Descargar estado de cuenta</p>
                        </div>
                        <svg class="w-4 h-4 text-gray-400 group-hover:text-emerald-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>

                    <button onclick="openPaymentChannelsModal()" class="w-full flex items-center gap-3 p-3 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors border border-gray-200 group">
                        <div class="w-9 h-9 bg-blue-600 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                        </div>
                        <div class="flex-1 text-left">
                            <p class="text-sm font-medium text-gray-900">Canales de Pago</p>
                            <p class="text-xs text-gray-500">Ver opciones disponibles</p>
                        </div>
                        <svg class="w-4 h-4 text-gray-400 group-hover:text-blue-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>

                    <a href="{{ route('pedidos.list') }}" class="flex items-center gap-3 p-3 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors border border-gray-200 group">
                        <div class="w-9 h-9 bg-gray-600 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900">Mis Pedidos</p>
                            <p class="text-xs text-gray-500">Ver historial</p>
                        </div>
                        <svg class="w-4 h-4 text-gray-400 group-hover:text-gray-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Facturas Pendientes -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b">
            <h2 class="text-xl font-semibold text-gray-800">Facturas Pendientes</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <input type="checkbox" id="selectAll" onclick="toggleSelectAll(this)" class="rounded border-gray-300">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nº Factura</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pedido</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Venc.</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Saldo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado de Pagos</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($accounts as $account)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-4 whitespace-nowrap">
                                @if($account->status != 'paid' && $account->balance > 0)
                                    <input type="checkbox" 
                                           class="invoice-checkbox rounded border-gray-300" 
                                           data-invoice-id="{{ $account->id }}"
                                           data-invoice-number="{{ $account->invoice_number }}"
                                           data-balance="{{ $account->balance }}"
                                           onchange="toggleInvoiceSelection(this)">
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $account->invoice_number }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                #{{ str_pad($account->sale_id, 6, '0', STR_PAD_LEFT) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $account->due_date ? $account->due_date->format('d/m/Y') : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                ${{ number_format($account->amount, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-red-600">
                                ${{ number_format($account->balance, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($account->status === 'paid')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Pagado</span>
                                @elseif($account->status === 'partial')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Parcial</span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Pendiente</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $pendingPayments = $account->payments()->where('amount', 0)->count();
                                    $approvedPayments = $account->payments()->where('amount', '>', 0)->count();
                                @endphp
                                
                                @if($pendingPayments > 0)
                                    <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800" title="{{ $pendingPayments }} pago(s) pendiente(s) de aprobación">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Pendiente ({{ $pendingPayments }})
                                    </span>
                                @elseif($approvedPayments > 0)
                                    <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Aprobado
                                    </span>
                                @else
                                    <span class="text-sm text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($account->balance > 0)
                                    <button onclick="openUploadModal({{ $account->id }}, '{{ $account->invoice_number }}')" 
                                            class="inline-flex items-center px-3 py-1 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                        </svg>
                                        Subir Comprobante
                                    </button>
                                @else
                                    <span class="text-sm text-green-600 font-medium">✓ Pagado</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-10 text-center text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p class="mt-2">No tienes facturas pendientes</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($accounts->hasPages())
            <div class="px-6 py-4 bg-gray-50">
                {{ $accounts->links() }}
            </div>
        @endif
    </div>

    <!-- Banner de Información de Pago -->
    <div id="paymentInfoBanner" class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg shadow-lg p-5 mt-6">
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-start flex-1">
                <div class="flex-shrink-0 w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center mr-4">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-white mb-1">Información Importante</p>
                    <p class="text-sm text-blue-50 leading-relaxed">
                        Recuerde subir el comprobante de pago después de realizar la transferencia. 
                        El pago será verificado y aprobado por nuestro equipo administrativo.
                    </p>
                </div>
            </div>
            <button onclick="openPaymentChannelsModal()" 
                    class="ml-4 bg-white text-blue-700 font-semibold px-5 py-2.5 rounded-md hover:bg-blue-50 transition-colors duration-200 shadow-md whitespace-nowrap flex items-center gap-2">
                
                Canales de Pago
            </button>
        </div>
    </div>
</div>

<!-- Botón Flotante para Pago Múltiple -->
<div id="multiplePaymentButton" class="hidden fixed bottom-8 left-8 z-40">
    <button onclick="openMultiplePaymentModal()" 
            class="flex items-center gap-2 px-6 py-4 bg-gradient-to-r from-emerald-600 to-emerald-700 text-white font-bold rounded-full shadow-2xl hover:from-emerald-700 hover:to-emerald-800 transition-all transform hover:scale-105">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>
        <span id="multiplePaymentButtonText">Pagar Facturas Seleccionadas (0)</span>
    </button>
</div>

<!-- Modal para Pago Múltiple -->
<div id="multiplePaymentModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-10 mx-auto p-6 border w-full max-w-3xl shadow-lg rounded-lg bg-white">
        <div class="mt-3">
            <h3 class="text-2xl font-bold text-gray-900 mb-4">Pago Múltiple de Facturas</h3>
            
            <!-- Resumen de facturas seleccionadas -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <h4 class="font-semibold text-gray-800 mb-3">Facturas Seleccionadas:</h4>
                <ul id="selectedInvoicesList" class="space-y-2 mb-3"></ul>
                <div class="border-t border-blue-300 pt-3 mt-3">
                    <p class="text-lg font-bold text-gray-900">Total Adeudado: <span id="totalDebt" class="text-blue-600"></span></p>
                </div>
            </div>
            
            <form id="multiplePaymentForm" action="{{ route('b2b.payment.upload.multiple') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div id="selectedInvoicesInputs"></div>
                
                <!-- Monto del Pago -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Monto Total del Pago *</label>
                    <input type="number" 
                           name="payment_amount" 
                           id="paymentAmount"
                           step="0.01" 
                           min="0.01"
                           required
                           onchange="updateDistributionPreview()"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="Ej: 100000.00">
                </div>
                
                <!-- Tipo de Distribución -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">Tipo de Distribución *</label>
                    <div class="space-y-2">
                        <label class="flex items-center p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                            <input type="radio" 
                                   name="distribution_type" 
                                   value="auto" 
                                   checked
                                   onchange="updateDistributionPreview()"
                                   class="mr-3">
                            <div>
                                <span class="font-medium text-gray-900">Distribución Automática</span>
                                <p class="text-sm text-gray-500">Paga las facturas de más antigua a más nueva</p>
                            </div>
                        </label>
                        <label class="flex items-center p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                            <input type="radio" 
                                   name="distribution_type" 
                                   value="manual"
                                   onchange="updateDistributionPreview()"
                                   class="mr-3">
                            <div>
                                <span class="font-medium text-gray-900">Distribución Manual</span>
                                <p class="text-sm text-gray-500">Especifica el monto para cada factura</p>
                            </div>
                        </label>
                    </div>
                </div>
                
                <!-- Preview de Distribución -->
                <div id="distributionPreview" class="mb-6 p-4 bg-gray-50 border border-gray-200 rounded-lg"></div>
                
                <!-- Campos Manuales (ocultos por defecto) -->
                <div id="manualDistribution" class="hidden mb-6"></div>
                
                <!-- Upload Comprobante -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Comprobante de Pago *</label>
                    <input type="file" 
                           name="payment_proof" 
                           accept=".pdf,.jpg,.jpeg,.png" 
                           required
                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-3 file:px-6 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <p class="mt-2 text-xs text-gray-500">PDF o imagen (JPG, PNG) - Máximo 5MB</p>
                </div>
                
                <!-- Botones -->
                <div class="flex items-center justify-end gap-3">
                    <button type="button" 
                            onclick="closeMultiplePaymentModal()" 
                            class="px-6 py-3 bg-gray-200 text-gray-800 font-medium rounded-lg hover:bg-gray-300 transition">
                        Cancelar
                    </button>
                    <button type="submit" 
                            class="px-6 py-3 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 transition shadow-lg">
                        Confirmar Pago
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Canales de Pago (Profesional - Sector Salud) -->
<div id="paymentChannelsModal" class="hidden fixed bottom-6 right-6 z-50 transition-all duration-300">
    <div class="bg-white rounded-lg shadow-2xl border border-gray-200 overflow-hidden" style="width: 380px; max-height: 85vh;">
        <!-- Header Profesional -->
        <div class="bg-blue-700 px-4 py-3 flex items-center justify-between cursor-pointer" onclick="toggleMinimizeModal()">
            <h3 class="text-sm font-semibold text-white">
                Información de Pago
            </h3>
            <div class="flex items-center gap-2">
                <button onclick="event.stopPropagation(); toggleMinimizeModal()" class="text-white hover:text-blue-100 transition-colors">
                    <svg id="minimizeIcon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <button onclick="event.stopPropagation(); closePaymentChannelsModal()" class="text-white hover:text-blue-100 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Content -->
        <div id="modalContent" class="overflow-y-auto bg-gray-50" style="max-height: calc(85vh - 50px);">
            <div class="p-4 space-y-3">
                @if($paymentMethods->count() > 0)
                    @foreach($paymentMethods as $method)
                        <!-- Transferencia Bancaria -->
                        @if($method->type === 'bank_account')
                            <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                                <div class="bg-gradient-to-r from-blue-50 to-blue-100 px-3 py-2 border-b border-blue-200">
                                    <h4 class="text-sm font-semibold text-gray-900">Transferencia Bancaria</h4>
                                </div>
                                <div class="p-3">
                                    <table class="w-full text-xs">
                                        <tbody class="divide-y divide-gray-100">
                                            <tr>
                                                <td class="py-2 text-gray-600 font-medium">Banco</td>
                                                <td class="py-2 text-right text-gray-900 font-semibold">{{ $method->bank_name }}</td>
                                            </tr>
                                            <tr>
                                                <td class="py-2 text-gray-600 font-medium">Tipo de Cuenta</td>
                                                <td class="py-2 text-right text-gray-900 font-semibold">{{ $method->getAccountTypeLabel() }}</td>
                                            </tr>
                                            <tr>
                                                <td class="py-2 text-gray-600 font-medium">Número</td>
                                                <td class="py-2 text-right text-gray-900 font-mono font-semibold">{{ $method->account_number }}</td>
                                            </tr>
                                            <tr>
                                                <td class="py-2 text-gray-600 font-medium">Titular</td>
                                                <td class="py-2 text-right text-gray-900 font-semibold">{{ $method->account_holder }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                        <!-- PSE -->
                        @if($method->type === 'payment_link' && $method->payment_link)
                            <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                                <div class="bg-gradient-to-r from-emerald-50 to-emerald-100 px-3 py-2 border-b border-emerald-200">
                                    <h4 class="text-sm font-semibold text-gray-900">Pago Electrónico PSE</h4>
                                </div>
                                <div class="p-3">
                                    <p class="text-xs text-gray-600 mb-2 leading-relaxed">
                                        Realice su pago de forma segura directamente desde su entidad bancaria
                                    </p>
                                    <a href="{{ $method->payment_link }}" target="_blank" class="w-full bg-emerald-600 text-white font-medium py-2 px-3 rounded-md hover:bg-emerald-700 transition-colors duration-200 flex items-center justify-center gap-2 text-xs">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                        </svg>
                                        Pagar con PSE
                                    </a>
                                    @if($method->description)
                                        <p class="text-xs text-gray-500 text-center mt-2">{{ $method->description }}</p>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <!-- QR -->
                        @if($method->type === 'qr_code' && $method->qr_code_image)
                            <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                                <div class="bg-gradient-to-r from-purple-50 to-purple-100 px-3 py-2 border-b border-purple-200">
                                    <h4 class="text-sm font-semibold text-gray-900">Código QR</h4>
                                </div>
                                <div class="p-3">
                                    <div class="bg-gray-50 rounded-lg p-3 mb-2 flex justify-center border border-gray-200">
                                        <img src="{{ Storage::url($method->qr_code_image) }}" alt="Código QR" class="w-28 h-28 rounded-md">
                                    </div>
                                    <p class="text-xs text-gray-600 text-center leading-relaxed">Escanee el código con la aplicación de su banco</p>
                                </div>
                            </div>
                        @endif
                    @endforeach
                @else
                    <div class="bg-white rounded-lg border border-gray-200 p-6 text-center">
                        <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-sm text-gray-600">No hay métodos de pago configurados</p>
                        <p class="text-xs text-gray-500 mt-2">Contacte al administrador para configurar los métodos de pago</p>
                    </div>
                @endif
            </div>

            <!-- Footer Note -->
            <div class="bg-blue-50 border-t border-blue-100 px-4 py-3">
                <div class="flex items-start gap-2">
                    <svg class="w-4 h-4 text-blue-700 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <p class="text-xs font-semibold text-blue-900 mb-0.5">Nota Importante</p>
                        <p class="text-xs text-blue-800 leading-relaxed">
                            Después de realizar su pago, por favor suba el comprobante para su verificación y aprobación.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para subir comprobante -->
<div id="uploadModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Subir Comprobante de Pago</h3>
            <p class="text-sm text-gray-500 mb-4">Factura: <span id="modalInvoiceNumber" class="font-semibold"></span></p>
            
            <form id="uploadForm" action="{{ route('b2b.payment.upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="account_id" id="accountId">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Comprobante de Pago</label>
                    <input type="file" name="payment_proof" accept=".pdf,.jpg,.jpeg,.png" required
                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <p class="mt-1 text-xs text-gray-500">PDF o imagen (JPG, PNG) - Máx. 5MB</p>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <button type="button" onclick="closeUploadModal()" 
                            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">
                        Cancelar
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Subir Comprobante
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function openUploadModal(accountId, invoiceNumber) {
    document.getElementById('accountId').value = accountId;
    document.getElementById('modalInvoiceNumber').textContent = invoiceNumber;
    document.getElementById('uploadModal').classList.remove('hidden');
}

function closeUploadModal() {
    document.getElementById('uploadModal').classList.add('hidden');
    document.getElementById('uploadForm').reset();
}

// Cerrar modal al hacer click fuera
document.getElementById('uploadModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeUploadModal();
    }
});

// ===== FUNCIONALIDAD DE PAGO MÚLTIPLE =====

let selectedInvoices = new Map();

function toggleInvoiceSelection(checkbox) {
    const invoiceId = checkbox.dataset.invoiceId;
    const invoiceNumber = checkbox.dataset.invoiceNumber;
    const balance = parseFloat(checkbox.dataset.balance);
    
    if (checkbox.checked) {
        selectedInvoices.set(invoiceId, {
            id: invoiceId,
            number: invoiceNumber,
            balance: balance
        });
    } else {
        selectedInvoices.delete(invoiceId);
    }
    
    updateMultiplePaymentButton();
}

function toggleSelectAll(checkbox) {
    const checkboxes = document.querySelectorAll('.invoice-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = checkbox.checked;
        toggleInvoiceSelection(cb);
    });
}

function updateMultiplePaymentButton() {
    const button = document.getElementById('multiplePaymentButton');
    const buttonText = document.getElementById('multiplePaymentButtonText');
    const count = selectedInvoices.size;
    
    if (count > 0) {
        buttonText.textContent = `Pagar Facturas Seleccionadas (${count})`;
        button.classList.remove('hidden');
    } else {
        button.classList.add('hidden');
    }
}

function openMultiplePaymentModal() {
    if (selectedInvoices.size === 0) {
        alert('Por favor selecciona al menos una factura');
        return;
    }
    
    // Llenar lista de facturas
    const list = document.getElementById('selectedInvoicesList');
    const inputsContainer = document.getElementById('selectedInvoicesInputs');
    let totalDebt = 0;
    let html = '';
    let inputsHtml = '';
    
    selectedInvoices.forEach(invoice => {
        totalDebt += invoice.balance;
        html += `
            <li class="flex justify-between items-center text-sm">
                <span class="font-medium text-gray-700">${invoice.number}</span>
                <span class="font-semibold text-gray-900">$${invoice.balance.toLocaleString('es-CO', {minimumFractionDigits: 2})}</span>
            </li>
        `;
        
        // Agregar inputs hidden para enviar IDs
        inputsHtml += `<input type="hidden" name="account_ids[]" value="${invoice.id}">`;
    });
    
    list.innerHTML = html;
    inputsContainer.innerHTML = inputsHtml;
    document.getElementById('totalDebt').textContent = '$' + totalDebt.toLocaleString('es-CO', {minimumFractionDigits: 2});
    
    // Limpiar campos
    document.getElementById('paymentAmount').value = '';
    document.getElementById('distributionPreview').innerHTML = '<p class="text-gray-500 text-sm">Ingresa el monto del pago para ver la distribución</p>';
    
    document.getElementById('multiplePaymentModal').classList.remove('hidden');
}

function closeMultiplePaymentModal() {
    document.getElementById('multiplePaymentModal').classList.add('hidden');
    document.getElementById('multiplePaymentForm').reset();
}

function updateDistributionPreview() {
    const amount = parseFloat(document.getElementById('paymentAmount').value) || 0;
    const type = document.querySelector('input[name="distribution_type"]:checked').value;
    
    if (amount <= 0) {
        document.getElementById('distributionPreview').innerHTML = '<p class="text-gray-500 text-sm">Ingresa el monto del pago para ver la distribución</p>';
        return;
    }
    
    if (type === 'auto') {
        showAutoDistribution(amount);
        document.getElementById('manualDistribution').classList.add('hidden');
    } else {
        showManualDistribution(amount);
        document.getElementById('manualDistribution').classList.remove('hidden');
    }
}

function showAutoDistribution(totalAmount) {
    const invoices = Array.from(selectedInvoices.values()).sort((a, b) => a.id - b.id);
    let remaining = totalAmount;
    const preview = document.getElementById('distributionPreview');
    
    let html = '<h4 class="font-semibold text-gray-800 mb-3">Distribución Automática:</h4><ul class="space-y-2">';
    
    for (const inv of invoices) {
        if (remaining <= 0) break;
        
        const toPay = Math.min(inv.balance, remaining);
        const status = toPay >= inv.balance ? '✓ Completo' : '⚠ Parcial';
        const statusColor = toPay >= inv.balance ? 'text-green-600' : 'text-yellow-600';
        
        html += `
            <li class="flex justify-between items-center text-sm">
                <span class="text-gray-700">${inv.number}:</span>
                <span class="font-semibold text-gray-900">$${toPay.toLocaleString('es-CO', {minimumFractionDigits: 2})} <span class="${statusColor} text-xs">${status}</span></span>
            </li>
        `;
        
        remaining -= toPay;
    }
    
    html += '</ul>';
    
    if (remaining > 0) {
        html += `<p class="text-yellow-600 text-sm mt-3 font-medium">⚠ Sobrante: $${remaining.toLocaleString('es-CO', {minimumFractionDigits: 2})}</p>`;
    }
    
    preview.innerHTML = html;
}

function showManualDistribution(totalAmount) {
    const invoices = Array.from(selectedInvoices.values());
    const container = document.getElementById('manualDistribution');
    
    let html = '<h4 class="font-semibold text-gray-800 mb-3">Especificar Monto por Factura:</h4><div class="space-y-3">';
    
    invoices.forEach(inv => {
        html += `
            <div class="flex items-center gap-3">
                <label class="flex-1 text-sm font-medium text-gray-700">${inv.number} (Saldo: $${inv.balance.toLocaleString('es-CO', {minimumFractionDigits: 2})})</label>
                <input type="number" 
                       name="distribution[${inv.id}]" 
                       max="${inv.balance}"
                       step="0.01"
                       min="0"
                       onchange="validateManualTotal()"
                       class="w-40 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                       placeholder="0.00">
            </div>
        `;
    });
    
    html += '</div>';
    container.innerHTML = html;
    
    // Actualizar preview inicial
    document.getElementById('distributionPreview').innerHTML = '<p class="text-gray-500 text-sm">Especifica los montos para cada factura</p>';
}

function validateManualTotal() {
    const totalAmount = parseFloat(document.getElementById('paymentAmount').value) || 0;
    const inputs = document.querySelectorAll('[name^="distribution"]');
    
    let sum = 0;
    inputs.forEach(input => {
        sum += parseFloat(input.value) || 0;
    });
    
    const diff = Math.abs(sum - totalAmount);
    const isValid = diff < 0.01;
    
    const preview = document.getElementById('distributionPreview');
    
    if (isValid) {
        preview.innerHTML = `
            <p class="text-green-600 font-medium">
                ✓ Total distribuido: $${sum.toLocaleString('es-CO', {minimumFractionDigits: 2})}
            </p>
        `;
    } else {
        preview.innerHTML = `
            <p class="text-red-600 font-medium">
                 Total distribuido: $${sum.toLocaleString('es-CO', {minimumFractionDigits: 2})}
                <br>
                <span class="text-sm">Diferencia: $${diff.toFixed(2)} (debe coincidir con el monto total del pago)</span>
            </p>
        `;
    }
    
    return isValid;
}

// Cerrar modal de pago múltiple al hacer click fuera
document.getElementById('multiplePaymentModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeMultiplePaymentModal();
    }
});

// Validar antes de enviar formulario
document.getElementById('multiplePaymentForm').addEventListener('submit', function(e) {
    const type = document.querySelector('input[name="distribution_type"]:checked').value;
    
    if (type === 'manual' && !validateManualTotal()) {
        e.preventDefault();
        alert('La suma de los montos debe coincidir con el monto total del pago');
        return false;
    }
    
    // Mostrar overlay de cargando
    const overlay = document.createElement('div');
    overlay.id = 'loadingOverlay';
    overlay.className = 'fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center z-50';
    overlay.innerHTML = `
        <div class="bg-white rounded-lg p-8 max-w-sm mx-4 text-center">
            <div class="animate-spin rounded-full h-16 w-16 border-b-4 border-blue-600 mx-auto mb-4"></div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Procesando Pago...</h3>
            <p class="text-gray-600">Por favor espera mientras registramos tu pago</p>
        </div>
    `;
    document.body.appendChild(overlay);
});

// ===== TOAST NOTIFICATIONS =====

function closeToast() {
    const successToast = document.getElementById('successToast');
    const errorToast = document.getElementById('errorToast');
    
    if (successToast) {
        successToast.style.transform = 'translateX(500px)';
        successToast.style.opacity = '0';
        setTimeout(() => successToast.remove(), 300);
    }
    
    if (errorToast) {
        errorToast.style.transform = 'translateX(500px)';
        errorToast.style.opacity = '0';
        setTimeout(() => errorToast.remove(), 300);
    }
}

// Auto-dismiss toasts después de 5 segundos
document.addEventListener('DOMContentLoaded', function() {
    const successToast = document.getElementById('successToast');
    const errorToast = document.getElementById('errorToast');
    
    if (successToast) {
        setTimeout(() => closeToast(), 5000);
    }
    
    if (errorToast) {
        setTimeout(() => closeToast(), 5000);
    }
});

// ===== MODAL DE CANALES DE PAGO =====

let isModalMinimized = false;

function openPaymentChannelsModal() {
    const modal = document.getElementById('paymentChannelsModal');
    const banner = document.getElementById('paymentInfoBanner');
    
    modal.classList.remove('hidden');
    banner.classList.add('hidden'); // SÍ ocultar el banner cuando modal se abre
    
    isModalMinimized = false;
    updateModalState();
}

function closePaymentChannelsModal() {
    const modal = document.getElementById('paymentChannelsModal');
    const banner = document.getElementById('paymentInfoBanner');
    
    modal.classList.add('hidden');
    banner.classList.remove('hidden'); // Mostrar banner cuando modal se cierra
}

function toggleMinimizeModal() {
    isModalMinimized = !isModalMinimized;
    updateModalState();
}

function updateModalState() {
    const content = document.getElementById('modalContent');
    const icon = document.getElementById('minimizeIcon');
    const modalContainer = document.getElementById('paymentChannelsModal').querySelector('div');
    
    if (isModalMinimized) {
        content.style.display = 'none';
        icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>';
        // Hacer el modal más pequeño cuando está minimizado
        modalContainer.style.width = '280px';
    } else {
        content.style.display = 'block';
        icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>';
        // Restaurar tamaño completo (más compacto)
        modalContainer.style.width = '380px';
    }
}

// ===== EXPORTAR A EXCEL =====
function exportToExcel() {
    // Buscar la tabla de facturas directamente
    const table = document.querySelector('.min-w-full.divide-y.divide-gray-200');
    if (!table) {
        alert('No hay facturas para exportar');
        return;
    }
    
    // Crear workbook simple (simulado con HTML)
    let html = '<table>';
    html += '<tr><th colspan="8" style="text-align:center;font-size:16px;font-weight:bold;">Estado de Cuenta - Facturas Pendientes</th></tr>';
    html += '<tr><th>N° Factura</th><th>Pedido</th><th>Fecha Venc.</th><th>Monto Total</th><th>Saldo</th><th>Estado</th><th>Estado de Pagos</th></tr>';
    
    const rows = table.querySelectorAll('tbody tr');
    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        if (cells.length > 0) {
            html += '<tr>';
            cells.forEach((cell, index) => {
                // Omitir columna de checkbox (0) y acciones (última)
                if (index !== 0 && index !== cells.length - 1) {
                    html += '<td>' + cell.textContent.trim() + '</td>';
                }
            });
            html += '</tr>';
        }
    });
    html += '</table>';
    
    // Crear blob y descargar
    const blob = new Blob([html], { type: 'application/vnd.ms-excel' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'estado_cuenta_' + new Date().toISOString().split('T')[0] + '.xls';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
}
</script>
@endpush
