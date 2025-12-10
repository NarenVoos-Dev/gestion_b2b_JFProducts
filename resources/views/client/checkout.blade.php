@extends('layouts.pos')

@section('title', 'Checkout - Confirmar Pedido')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-8">
    <div class="container mx-auto px-4 max-w-4xl">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Confirmar Pedido</h1>
            <p class="text-gray-600">Revisa tu pedido antes de confirmar</p>
        </div>

        <!-- Resumen del Carrito -->
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Resumen del Pedido</h2>
            
            <!-- Items del carrito -->
            <div id="checkoutItems" class="space-y-4 mb-6">
                <!-- Se llenará con JavaScript -->
            </div>

            <!-- Totales -->
            <div class="border-t pt-4">
                <div class="flex justify-between text-gray-600 mb-2">
                    <span>Subtotal:</span>
                    <span id="checkoutSubtotal" class="font-semibold">$0</span>
                </div>
                <div class="flex justify-between text-gray-600 mb-2">
                    <span>IVA:</span>
                    <span id="checkoutTax" class="font-semibold">$0</span>
                </div>
                <div class="flex justify-between text-xl font-bold text-gray-900 pt-2 border-t">
                    <span>Total:</span>
                    <span id="checkoutTotal" class="text-blue-600">$0</span>
                </div>
            </div>
        </div>

        <!-- Notas del Pedido -->
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Notas u Observaciones</h2>
            <textarea 
                id="orderNotes" 
                rows="4" 
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="Agrega cualquier nota o instrucción especial para tu pedido..."
            ></textarea>
        </div>

        <!-- Botones de Acción -->
        <div class="flex gap-4">
            <a href="/catalogo" class="flex-1 py-4 px-6 bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold rounded-xl transition-all text-center">
                ← Volver al Catálogo
            </a>
            <button 
                onclick="confirmOrder()" 
                class="flex-1 py-4 px-6 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-bold rounded-xl transition-all shadow-lg hover:shadow-xl"
            >
                Confirmar Pedido →
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Helper para hacer peticiones a la API
    function apiRequest(url, method = 'GET', data = null) {
        const options = {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
        };

        if (data && (method === 'POST' || method === 'PUT' || method === 'PATCH')) {
            options.body = JSON.stringify(data);
        }

        return fetch(url, options)
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => Promise.reject(err));
                }
                return response.json();
            });
    }

    // Cargar resumen del carrito
    function loadCheckoutSummary() {
        apiRequest('{{ url("/api/b2b/cart") }}')
            .then(response => {
                if (response.success) {
                    renderCheckoutItems(response.cart);
                    updateCheckoutTotals(response.summary);
                }
            })
            .catch(error => {
                console.error('Error al cargar carrito:', error);
                Swal.fire({
                    title: 'Error',
                    text: 'No se pudo cargar el carrito',
                    icon: 'error',
                    confirmButtonColor: '#0f4db3',
                }).then(() => {
                    window.location.href = '/catalogo';
                });
            });
    }

    // Renderizar items del carrito
    function renderCheckoutItems(items) {
        const container = document.getElementById('checkoutItems');
        
        if (items.length === 0) {
            container.innerHTML = `
                <div class="text-center py-8">
                    <p class="text-gray-500">Tu carrito está vacío</p>
                    <a href="/catalogo" class="text-blue-600 hover:underline mt-2 inline-block">Ir al catálogo</a>
                </div>
            `;
            return;
        }

        let html = '';
        items.forEach(item => {
            const lotInfo = item.lot_number 
                ? `<span class="text-xs bg-blue-100 text-blue-800 px-2 py-0.5 rounded">Lote: ${item.lot_number}</span>`
                : `<span class="text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded">Sin lote asignado</span>`;

            html += `
                <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-lg">
                    <img src="${item.image_url || '/img/no-image.png'}" 
                         alt="${item.name}" 
                         class="w-16 h-16 object-contain rounded"
                         onerror="this.src='/img/no-image.png'">
                    <div class="flex-1">
                        <h3 class="font-semibold text-gray-900">${item.name}</h3>
                        <p class="text-sm text-gray-600">${item.laboratory}</p>
                        ${lotInfo}
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-600">Cantidad: ${item.quantity}</p>
                        <p class="font-bold text-gray-900">$${parseFloat(item.price).toLocaleString('es-CO')}</p>
                        <p class="text-sm text-gray-600">Subtotal: $${parseFloat(item.subtotal).toLocaleString('es-CO')}</p>
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
    }

    // Actualizar totales
    function updateCheckoutTotals(summary) {
        document.getElementById('checkoutSubtotal').textContent = '$' + parseFloat(summary.subtotal).toLocaleString('es-CO');
        document.getElementById('checkoutTax').textContent = '$' + parseFloat(summary.tax).toLocaleString('es-CO');
        document.getElementById('checkoutTotal').textContent = '$' + parseFloat(summary.total).toLocaleString('es-CO');
    }

    // Confirmar pedido
    function confirmOrder() {
        const notes = document.getElementById('orderNotes').value;
        
        Swal.fire({
            title: '¿Confirmar pedido?',
            text: 'Se enviará tu pedido para procesamiento',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, confirmar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#0f4db3',
            cancelButtonColor: '#6c757d',
        }).then((result) => {
            if (result.isConfirmed) {
                // MOSTRAR LOADING
                Swal.fire({
                    title: 'Procesando pedido...',
                    html: `
                        <div class="flex flex-col items-center py-4">
                            <div class="animate-spin rounded-full h-16 w-16 border-b-4 border-blue-600 mb-4"></div>
                            <p class="text-gray-600">Por favor espera mientras creamos tu pedido</p>
                        </div>
                    `,
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // LLAMAR API
                apiRequest('{{ url("/api/b2b/checkout") }}', 'POST', { notes })
                    .then(response => {
                        if (response.success) {
                            // ALERT DE ÉXITO CON BOTONES
                            Swal.fire({
                                title: '¡Pedido Creado!',
                                html: `
                                    <div class="text-center">
                                        <div class="text-6xl mb-4">✅</div>
                                        <p class="text-lg font-semibold mb-2">Pedido #${response.order_number}</p>
                                        <p class="text-gray-600 mb-4">Tu pedido ha sido creado exitosamente y está siendo procesado.</p>
                                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4">
                                            <p class="text-sm text-blue-800">
                                                <strong>Total:</strong> $${response.total.toLocaleString('es-CO')}
                                            </p>
                                            <p class="text-sm text-blue-800">
                                                <strong>Items:</strong> ${response.item_count}
                                            </p>
                                        </div>
                                    </div>
                                `,
                                icon: 'success',
                                showCancelButton: true,
                                confirmButtonText: '💬 Hablar con asesor',
                                cancelButtonText: '✓ Finalizar',
                                confirmButtonColor: '#25D366', // Verde WhatsApp
                                cancelButtonColor: '#0f4db3',
                                reverseButtons: true,
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    // ABRIR WHATSAPP
                                    const phone = '573001234567'; // Genérico por ahora
                                    const message = `Hola! Acabo de crear el pedido #${response.order_number} por un total de $${response.total.toLocaleString('es-CO')}. ¿Podrías ayudarme?`;
                                    window.open(`https://wa.me/${phone}?text=${encodeURIComponent(message)}`, '_blank');
                                    
                                    // Redirigir a pedidos
                                    setTimeout(() => {
                                        window.location.href = '/pedidos';
                                    }, 1000);
                                } else {
                                    // FINALIZAR - IR A PEDIDOS
                                    window.location.href = '/pedidos';
                                }
                            });
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            title: 'Error',
                            text: error.message || 'No se pudo crear el pedido',
                            icon: 'error',
                            confirmButtonColor: '#0f4db3',
                        });
                    });
            }
        });
    }

    // Cargar resumen al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
        loadCheckoutSummary();
    });
</script>
@endpush
