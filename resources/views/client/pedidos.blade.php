@extends('layouts.pos')

@section('title', 'Mis Pedidos')

@push('styles')
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<style>
    
    /* Padding al wrapper para separar del borde */
    #pedidosTable_wrapper {
        padding: 1.5rem;
    }
    
    /* Estilos personalizados para DataTable */
    #pedidosTable_wrapper .dataTables_length select {
        @apply px-3 py-2 border border-gray-300 rounded-lg;
    }
    #pedidosTable_wrapper .dataTables_filter input {
        @apply px-3 py-2 border border-gray-300 rounded-lg;
    }
    
    /* Tabla sin bordes externos, solo divisores internos */
    #pedidosTable {
        border-collapse: separate !important;
        border-spacing: 0 !important;
        border: none !important;
    }
    
    #pedidosTable thead th {
        @apply bg-gradient-to-r from-blue-50 to-blue-100 px-6 py-4 text-left text-sm font-bold text-gray-800;
        border: none !important;
        border-bottom: 2px solid #e5e7eb !important;
    }
    
    #pedidosTable tbody td {
        @apply px-6 py-4 text-gray-700;
        border: none !important;
        border-bottom: 1px solid #f3f4f6 !important;
    }
    
    #pedidosTable tbody tr {
        transition: all 0.2s ease;
    }
    
    #pedidosTable tbody tr:hover {
        @apply bg-blue-50;
        transform: scale(1.01);
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    
    #pedidosTable tbody tr:last-child td {
        border-bottom: none !important;
    }
    
    /* Paginación */
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        @apply px-3 py-2 mx-1 rounded-lg border-0;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        @apply bg-blue-600 text-white;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        @apply bg-blue-100 text-blue-700;
        border: none !important;
    }
    
    /* Info y controles */
    .dataTables_wrapper .dataTables_info {
        @apply text-gray-600 font-medium;
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-8">
    <div class="container mx-auto px-4 max-w-7xl">
        <!-- Header -->
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Mis Pedidos</h1>
                <p class="text-gray-600">Historial y seguimiento de pedidos</p>
            </div>
            <a href="/catalogo" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-bold rounded-xl transition-all shadow-lg">
                + Nuevo Pedido
            </a>
        </div>

        <!-- Tarjetas de Resumen -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow p-6">
                <div class="text-sm text-gray-600 mb-1">Total de Pedidos</div>
                <div class="text-3xl font-bold text-gray-900" id="totalPedidos">0</div>
            </div>
            <div class="bg-white rounded-xl shadow p-6">
                <div class="text-sm text-gray-600 mb-1">Pedidos Pendientes</div>
                <div class="text-3xl font-bold text-yellow-600" id="pedidosPendientes">0</div>
            </div>
            <div class="bg-white rounded-xl shadow p-6">
                <div class="text-sm text-gray-600 mb-1">Total Gastado</div>
                <div class="text-3xl font-bold text-green-600" id="totalGastado">$0</div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="bg-white rounded-xl shadow p-6 mb-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Filtros</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Rango de Fechas -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Desde</label>
                    <input type="date" id="filterDateFrom" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Hasta</label>
                    <input type="date" id="filterDateTo" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                
                <!-- Estado -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                    <select id="filterStatus" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">Todos</option>
                        <option value="Pendiente">Pendiente</option>
                        <option value="Separación">Separación</option>
                        <option value="Facturado">Facturado</option>
                        <option value="Finalizado">Finalizado</option>
                    </select>
                </div>
                
                <!-- Búsqueda por Número -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nº Pedido</label>
                    <input type="text" id="filterOrderNumber" placeholder="Ej: 123" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            
            <div class="mt-4 flex gap-2">
                <button onclick="applyFilters()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold transition-all">
                    Aplicar Filtros
                </button>
                <button onclick="clearFilters()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-semibold transition-all">
                    Limpiar
                </button>
            </div>
        </div>

        <!-- DataTable -->
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <table id="pedidosTable" class="w-full">
                <thead>
                    <tr>
                        <th>Pedido</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>Subtotal</th>
                        <th>IVA</th>
                        <th>Total</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- Modal Overlay -->
<div id="orderDetailOverlay" class="fixed inset-0 bg-black/50 opacity-0 invisible transition-all duration-300 z-40" onclick="closeOrderDetail()"></div>

<!-- Modal Lateral -->
<div id="orderDetailModal" class="fixed right-0 inset-y-0 w-full md:w-4/4 lg:w-4/5 xl:w-4/5 bg-white shadow-2xl transform translate-x-full transition-transform duration-300 z-50 overflow-y-auto">
    <!-- Header -->
    <div class="sticky top-0 bg-white border-b px-6 py-4 flex justify-between items-center z-10">
        <h2 class="text-2xl font-bold text-gray-900" id="modalOrderTitle">Pedido #000000</h2>
        <button onclick="closeOrderDetail()" class="text-gray-500 hover:text-gray-700 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
    
    <!-- Contenido -->
    <div id="modalOrderContent" class="p-6">
        <!-- Se llenará dinámicamente -->
    </div>
</div>
@endsection

@push('scripts')
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<!-- Moment.js para formateo de fechas -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>

<script>
let pedidosTable;

$(document).ready(function() {
    // Inicializar DataTable
    pedidosTable = $('#pedidosTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ url("/api/b2b/pedidos/datatable") }}',
            data: function(d) {
                d.date_from = $('#filterDateFrom').val();
                d.date_to = $('#filterDateTo').val();
                d.status = $('#filterStatus').val();
                d.order_number = $('#filterOrderNumber').val();
            }
        },
        columns: [
            { 
                data: 'id',
                render: function(data) {
                    return '#' + String(data).padStart(6, '0');
                }
            },
            { 
                data: 'date',
                render: function(data) {
                    return moment(data).format('DD/MM/YYYY hh:mm A');
                }
            },
            { 
                data: 'status',
                render: function(data) {
                    const colors = {
                        'Pendiente': 'bg-yellow-100 text-yellow-800 border-yellow-200',
                        'Separación': 'bg-blue-100 text-blue-800 border-blue-200',
                        'Facturado': 'bg-green-100 text-green-800 border-green-200',
                        'Finalizado': 'bg-gray-100 text-gray-800 border-gray-200'
                    };
                    return `<span class="inline-flex px-3 py-1 rounded-full text-sm font-semibold border ${colors[data] || 'bg-gray-100 text-gray-800'}">${data}</span>`;
                }
            },
            { 
                data: 'subtotal',
                render: function(data) {
                    return '$' + parseFloat(data).toLocaleString('es-CO', {minimumFractionDigits: 0, maximumFractionDigits: 0});
                }
            },
            { 
                data: 'tax',
                render: function(data) {
                    return '$' + parseFloat(data).toLocaleString('es-CO', {minimumFractionDigits: 0, maximumFractionDigits: 0});
                }
            },
            { 
                data: 'total',
                render: function(data) {
                    return '$' + parseFloat(data).toLocaleString('es-CO', {minimumFractionDigits: 0, maximumFractionDigits: 0});
                }
            },
            {
                data: 'id',
                orderable: false,
                render: function(data) {
                    return `<button onclick="openOrderDetail(${data})" class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 font-semibold transition-all">
                        Ver Detalle
                    </button>`;
                }
            }
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
        },
        order: [[1, 'desc']], // Ordenar por fecha descendente
        pageLength: 25,
        drawCallback: function() {
            // Actualizar tarjetas después de cada dibujo
            updateSummaryCards();
        }
    });
    
    // Actualizar tarjetas al cargar
    updateSummaryCards();
});

function applyFilters() {
    pedidosTable.ajax.reload();
    updateSummaryCards(); // Actualizar tarjetas con filtros aplicados
}

function clearFilters() {
    $('#filterDateFrom').val('');
    $('#filterDateTo').val('');
    $('#filterStatus').val('');
    $('#filterOrderNumber').val('');
    applyFilters();
}

function updateSummaryCards() {
    const filters = {
        date_from: $('#filterDateFrom').val(),
        date_to: $('#filterDateTo').val(),
        status: $('#filterStatus').val(),
        order_number: $('#filterOrderNumber').val()
    };
    
    console.log('Actualizando tarjetas con filtros:', filters);
    
    $.get('{{ url("/api/b2b/pedidos/summary") }}', filters, function(data) {
        console.log('Datos recibidos:', data);
        $('#totalPedidos').text(data.total_pedidos);
        $('#pedidosPendientes').text(data.pedidos_pendientes);
        $('#totalGastado').text('$' + parseFloat(data.total_gastado).toLocaleString('es-CO', {minimumFractionDigits: 0, maximumFractionDigits: 0}));
    }).fail(function(error) {
        console.error('Error al actualizar tarjetas:', error);
    });
}

function openOrderDetail(orderId) {
    // Mostrar modal
    $('#orderDetailOverlay').removeClass('invisible opacity-0');
    $('#orderDetailModal').removeClass('translate-x-full');
    
    // LIMPIAR CONTENIDO ANTERIOR Y MOSTRAR LOADING
    $('#modalOrderTitle').text('Cargando...');
    $('#modalOrderContent').html(`
        <div class="flex flex-col items-center justify-center py-20">
            <div class="animate-spin rounded-full h-16 w-16 border-b-4 border-blue-600 mb-4"></div>
            <p class="text-gray-600 font-semibold">Cargando detalle del pedido...</p>
        </div>
    `);
    
    // Cargar datos
    $.get(`{{ url("/api/b2b/pedidos") }}/${orderId}`, function(data) {
        renderOrderDetail(data);
    }).fail(function() {
        $('#modalOrderContent').html(`
            <div class="flex flex-col items-center justify-center py-20">
                <div class="text-6xl mb-4">❌</div>
                <p class="text-red-600 font-semibold">Error al cargar el detalle del pedido</p>
                <button onclick="closeOrderDetail()" class="mt-4 px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300">
                    Cerrar
                </button>
            </div>
        `);
    });
}

function closeOrderDetail() {
    $('#orderDetailOverlay').addClass('invisible opacity-0');
    $('#orderDetailModal').addClass('translate-x-full');
}

function renderOrderDetail(order) {
    $('#modalOrderTitle').text('Pedido #' + String(order.id).padStart(6, '0'));
    
    const statusColors = {
        'Pendiente': 'bg-yellow-100 text-yellow-800 border-yellow-200',
        'Separación': 'bg-blue-100 text-blue-800 border-blue-200',
        'Facturado': 'bg-green-100 text-green-800 border-green-200',
        'Finalizado': 'bg-gray-100 text-gray-800 border-gray-200'
    };
    
    let html = `
        <!-- Fecha y Estado -->
        <div class="mb-6 flex justify-between items-center">
            <div>
                <p class="text-sm text-gray-600">Fecha</p>
                <p class="text-lg font-semibold">${moment(order.date).format('DD/MM/YYYY hh:mm A')}</p>
            </div>
            <span class="px-4 py-2 rounded-full text-lg font-semibold border ${statusColors[order.status] || 'bg-gray-100 text-gray-800'}">
                ${order.status}
            </span>
        </div>
        
        <!-- Items -->
        <h3 class="text-xl font-bold mb-4">Productos</h3>
        <div class="overflow-x-auto mb-6">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Producto</th>
                        <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Lote</th>
                        <th class="px-4 py-2 text-right text-sm font-semibold text-gray-700">Cant.</th>
                        <th class="px-4 py-2 text-right text-sm font-semibold text-gray-700">Precio</th>
                        <th class="px-4 py-2 text-right text-sm font-semibold text-gray-700">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
    `;
    
    order.items.forEach(item => {
        html += `
            <tr>
                <td class="px-4 py-3">
                    <div class="font-semibold text-gray-900">${item.product.name}</div>
                    <div class="text-sm text-gray-600">${item.product.laboratory?.name || 'N/A'}</div>
                </td>
                <td class="px-4 py-3">
                    ${item.lot_number ? 
                        `<span class="text-sm bg-blue-100 text-blue-800 px-2 py-1 rounded font-medium">${item.lot_number}</span>
                         ${item.expiration_date ? `<div class="text-xs text-gray-500 mt-1">Vence: ${moment(item.expiration_date).format('DD/MM/YYYY')}</div>` : ''}` : 
                        `<span class="text-xs text-amber-600 bg-amber-50 px-2 py-1 rounded">Sin lote</span>`
                    }
                </td>
                <td class="px-4 py-3 text-right font-semibold">${item.quantity}</td>
                <td class="px-4 py-3 text-right">$${parseFloat(item.price).toLocaleString('es-CO', {minimumFractionDigits: 0})}</td>
                <td class="px-4 py-3 text-right font-bold">$${(item.price * item.quantity).toLocaleString('es-CO', {minimumFractionDigits: 0})}</td>
            </tr>
        `;
    });
    
    html += `
                </tbody>
            </table>
        </div>
        
        <!-- Totales -->
        <div class="border-t pt-4 max-w-md ml-auto mb-6">
            <div class="flex justify-between mb-2 text-gray-600">
                <span>Subtotal:</span>
                <span class="font-semibold">$${parseFloat(order.subtotal).toLocaleString('es-CO', {minimumFractionDigits: 0})}</span>
            </div>
            <div class="flex justify-between mb-2 text-gray-600">
                <span>IVA:</span>
                <span class="font-semibold">$${parseFloat(order.tax).toLocaleString('es-CO', {minimumFractionDigits: 0})}</span>
            </div>
            <div class="flex justify-between text-2xl font-bold border-t pt-2">
                <span>Total:</span>
                <span class="text-blue-600">$${parseFloat(order.total).toLocaleString('es-CO', {minimumFractionDigits: 0})}</span>
            </div>
        </div>
    `;
    
    // NOTAS AL FINAL - SOLO SI EXISTEN
    if (order.notes) {
        html += `
        <div class="border-t pt-6">
            <h3 class="text-lg font-bold text-gray-900 mb-3">Notas del Pedido</h3>
            <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <p class="text-blue-900">${order.notes}</p>
            </div>
        </div>
        `;
    }
    
    $('#modalOrderContent').html(html);
}

// Cerrar modal con tecla ESC
$(document).keyup(function(e) {
    if (e.key === "Escape") {
        closeOrderDetail();
    }
});
</script>
@endpush
