@extends('layouts.pos')

@section('title', 'Catálogo de Productos')
@section('page-title', 'Catálogo de Productos')

@section('content')
<!-- Contenedor Principal del Título -->
<div class="bg-white/95 backdrop-blur-xl rounded-xl p-6 mb-4 shadow-lg border border-white/20">
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <h2 class="text-3xl font-extrabold bg-gradient-to-r from-[#0f4db3] to-[#028dff] bg-clip-text text-transparent mb-1">
                💊 Catálogo de Productos
            </h2>
            <p class="text-gray-600 text-sm font-medium">Inventario y precios exclusivos para clientes institucionales</p>
        </div>
        
        <!-- Contador de productos -->
        <div class="flex items-center gap-2 bg-gradient-to-br from-[#0f4db3]/10 to-[#028dff]/10 px-4 py-2 rounded-lg">
            <svg class="w-5 h-5 text-[#0f4db3]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
            <span class="text-sm font-semibold text-gray-700">
                <span id="productCount">0</span> productos disponibles
            </span>
        </div>
    </div>
</div>

<!-- Barra de Búsqueda y Filtros -->
<div class="bg-white/95 backdrop-blur-xl rounded-xl p-4 mb-4 shadow-lg border border-white/20">
    <div class="flex flex-col md:flex-row gap-4">
        <!-- Búsqueda -->
        <div class="flex-1 relative">
            <input 
                type="text" 
                id="searchInput" 
                placeholder="🔍 Buscar productos por nombre, principio activo, laboratorio..."
                class="w-full px-4 py-3 pl-12 rounded-lg border-2 border-gray-200 focus:border-[#0f4db3] focus:ring-2 focus:ring-[#0f4db3]/20 transition-all duration-300 outline-none"
            >
            <svg class="w-5 h-5 text-gray-400 absolute left-4 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>
        
        <!-- Botón limpiar búsqueda -->
        <button 
            id="clearSearch" 
            class="hidden px-4 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-all duration-300 font-medium"
        >
            ✕ Limpiar
        </button>
    </div>
</div>

<!-- Filtros de Categoría -->
<div class="bg-white/95 backdrop-blur-xl rounded-xl p-4 mb-4 shadow-lg border border-white/20">
    <div class="flex items-center gap-3 mb-3">
        <svg class="w-5 h-5 text-[#0f4db3]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
        </svg>
        <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wide">Filtrar por categoría</h3>
    </div>
    
    <div class="flex gap-2 flex-wrap">
        <button class="filter-tab active px-4 py-2 rounded-lg border-2 border-[#0f4db3] bg-gradient-to-br from-[#0f4db3] to-[#028dff] text-white cursor-pointer transition-all duration-300 font-medium text-sm shadow-md hover:shadow-lg" data-category-id="all">
            📦 Todos
        </button>
        
        @foreach($categories as $category)
            <button class="filter-tab px-4 py-2 rounded-lg border-2 border-gray-200 bg-white text-gray-700 cursor-pointer transition-all duration-300 font-medium text-sm hover:border-[#0f4db3] hover:bg-[#0f4db3]/5 hover:text-[#0f4db3]" data-category-id="{{ $category->id }}">
                {{ $category->name }}
            </button>
        @endforeach
    </div>
</div>

<!-- Grid de Productos -->
<div id="productsGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-6">
</div>
@endsection
@push('scripts')
<script>

    let allProducts = [];
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    // funcion para hacer peticiones al servidor 
    function ajaxRequest(url, method, data = {}) {
        return $.ajax({
            url: url,
            method: method,
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            data: data
        }).fail(function(xhr) {
            console.error('Error AJAX:', xhr);
            const error = xhr.responseJSON;
            showAlert('Error Inesperado', error?.message || 'Problema de comunicación con el servidor.', 'error');
        });
    }
    document.addEventListener('DOMContentLoaded', function() {
        // Guardamos los productos iniciales que pasamos desde el controlador
        allProducts = @json($products);
        //console.log(allProducts)
        const productsGrid = document.getElementById('productsGrid');
        const csrfToken = $('meta[name="csrf-token"]').attr('content');

        // Función para renderizar los productos en el HTML
        function renderProducts(productsToRender) {
            const grid = document.getElementById('productsGrid');
            
            // Actualizar contador de productos
            document.getElementById('productCount').textContent = productsToRender?.length || 0;
            
            if (!productsToRender || productsToRender.length === 0) {
                productsGrid.innerHTML = `
                    <div class="col-span-full flex flex-col items-center justify-center py-20 px-4">
                        <div class="w-32 h-32 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center mb-6 relative animate-pulse">
                            <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <div class="absolute -bottom-2 -right-2 w-12 h-12 bg-white rounded-full flex items-center justify-center shadow-lg">
                                <span class="text-2xl">📦</span>
                            </div>
                        </div>
                        
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">No encontramos productos</h3>
                        <p class="text-gray-600 mb-6 text-center max-w-md">
                            No hay productos disponibles para esta búsqueda o categoría.
                        </p>
                    </div>
                `;
                return;
            }

            grid.innerHTML = productsToRender.map(product => {
                // Lógica para determinar el texto y color del stock
                const stock = parseFloat(product.stock_in_location ?? 0);
                const stock_minimo = parseFloat(product.stock_minimo ?? 10);
                
                let stockText = '';
                let stockColorClass = '';
                let stockPercentage = 0;

                if (stock > stock_minimo) {
                    stockText = `${stock} unidades`;
                    stockColorClass = 'bg-green-500';
                    stockPercentage = Math.min(100, (stock / (stock_minimo * 2)) * 100);
                } else if (stock <= stock_minimo && stock > 0) {
                    stockText = `${stock} unidades (bajo)`;
                    stockColorClass = 'bg-orange-500';
                    stockPercentage = (stock / stock_minimo) * 100;
                } else {
                    stockText = 'Agotado';
                    stockColorClass = 'bg-red-500';
                    stockPercentage = 0;
                }

                const price = parseFloat(product.sale_price || product.price || 0);

                // Badges informativos
                let badges = '';
                if (product.controlled) {
                    badges += `<span class="inline-flex items-center gap-1 px-2 py-1 bg-red-100 text-red-700 rounded-md text-xs font-semibold">
                        ⚠️ Controlado
                    </span>`;
                }
                if (product.cold_chain) {
                    badges += `<span class="inline-flex items-center gap-1 px-2 py-1 bg-blue-100 text-blue-700 rounded-md text-xs font-semibold">
                        ❄️ Cadena frío
                    </span>`;
                }
                if (product.regulated) {
                    badges += `<span class="inline-flex items-center gap-1 px-2 py-1 bg-purple-100 text-purple-700 rounded-md text-xs font-semibold">
                        📋 Regulado
                    </span>`;
                }

                // Construcción del HTML de la tarjeta mejorada
                return `
                    <div class="group bg-white rounded-2xl shadow-md hover:shadow-2xl transition-all duration-500 cursor-pointer relative overflow-hidden border border-gray-100 hover:border-[#0f4db3]/30 transform hover:-translate-y-2"
                         onclick="openProductModal(${product.id})">
                        
                        <!-- Barra superior decorativa -->
                        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-[#0f4db3] to-[#028dff] transform scale-x-0 group-hover:scale-x-100 transition-transform duration-500"></div>
                        
                        <!-- Imagen del producto con gradiente -->
                        <div class="relative h-48 bg-gradient-to-br from-gray-50 to-gray-100 flex items-center justify-center overflow-hidden">
                            <div class="absolute inset-0 bg-gradient-to-br from-[#0f4db3]/5 to-[#028dff]/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                            <span class="text-7xl relative z-10 transform group-hover:scale-110 transition-transform duration-500">
                                ${product.image ?? '�'}
                            </span>
                            
                            <!-- Badge de stock en esquina -->
                            <div class="absolute top-3 right-3 ${stockColorClass} text-white px-3 py-1 rounded-full text-xs font-bold shadow-lg">
                                ${stock > 0 ? '✓ Stock' : '✕ Agotado'}
                            </div>
                        </div>
                        
                        <!-- Contenido -->
                        <div class="p-5">
                            <!-- Nombre del producto -->
                            <h3 class="text-base font-bold text-gray-900 mb-2 leading-tight line-clamp-2 min-h-[48px] group-hover:text-[#0f4db3] transition-colors duration-300">
                                ${product.name}
                            </h3>
                            
                            <!-- Badges informativos -->
                            ${badges ? `<div class="flex flex-wrap gap-1 mb-3">${badges}</div>` : ''}
                            
                            <!-- Información adicional -->
                            <div class="space-y-2 mb-4">
                                ${product.laboratory?.name ? `
                                    <div class="flex items-center gap-2 text-xs text-gray-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                        </svg>
                                        <span class="font-medium">${product.laboratory.name}</span>
                                    </div>
                                ` : ''}
                                
                                <!-- Barra de stock visual -->
                                <div class="space-y-1">
                                    <div class="flex justify-between items-center text-xs">
                                        <span class="text-gray-600 font-medium">Stock disponible</span>
                                        <span class="font-bold ${stock > stock_minimo ? 'text-green-600' : stock > 0 ? 'text-orange-600' : 'text-red-600'}">
                                            ${stockText}
                                        </span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                                        <div class="${stockColorClass} h-full rounded-full transition-all duration-500" style="width: ${stockPercentage}%"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Precio -->
                            <div class="mb-4">
                                <div class="text-3xl font-extrabold bg-gradient-to-r from-[#0f4db3] to-[#028dff] bg-clip-text text-transparent">
                                    $${price.toLocaleString('es-CO')}
                                </div>
                                <div class="text-xs text-gray-500">Precio por unidad</div>
                            </div>
                            
                            <!-- Botones -->
                            <div class="flex gap-2">
                                <button onclick="event.stopPropagation(); openProductModal(${product.id})" 
                                        class="flex-1 px-4 py-2.5 rounded-lg font-semibold text-sm border-2 border-gray-300 text-gray-700 bg-white hover:border-[#0f4db3] hover:bg-[#0f4db3]/5 hover:text-[#0f4db3] transition-all duration-300 inline-flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                    Ver
                                </button>
                                
                                <button onclick="event.stopPropagation(); quickAddToCart(${product.id})" 
                                        class="flex-1 px-4 py-2.5 rounded-lg font-semibold text-sm bg-gradient-to-br from-[#0f4db3] to-[#028dff] text-white border-none hover:shadow-lg hover:shadow-[#0f4db3]/40 transform hover:scale-105 transition-all duration-300 inline-flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="9" cy="21" r="1"></circle>
                                        <circle cx="20" cy="21" r="1"></circle>
                                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                                    </svg>
                                    Agregar
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        }

        function loadProducts(categoryId = null, searchTerm = '') {
            //cargando productos
            productsGrid.innerHTML = `
                    <div class="col-span-full flex flex-col items-center justify-center py-16">
                        <div class="relative w-20 h-20 mb-6">
                            <!-- Spinner animado -->
                            <div class="absolute inset-0 border-4 border-[#0f4db3]/20 rounded-full"></div>
                            <div class="absolute inset-0 border-4 border-transparent border-t-[#0f4db3] rounded-full animate-spin"></div>
                            <div class="absolute inset-2 border-4 border-transparent border-t-[#028dff] rounded-full animate-spin" style="animation-duration: 0.8s; animation-direction: reverse;"></div>
                        </div>
                        
                        <!-- Texto con animación de puntos -->
                        <p class="text-lg font-semibold text-gray-700 flex items-center gap-2">
                            <span>Cargando productos</span>
                            <span class="flex gap-1">
                                <span class="w-2 h-2 bg-[#0f4db3] rounded-full animate-bounce" style="animation-delay: 0ms;"></span>
                                <span class="w-2 h-2 bg-[#0f4db3] rounded-full animate-bounce" style="animation-delay: 150ms;"></span>
                                <span class="w-2 h-2 bg-[#0f4db3] rounded-full animate-bounce" style="animation-delay: 300ms;"></span>
                            </span>
                        </p>
                        
                        <p class="text-sm text-gray-500 mt-2">Esto solo tomará un momento</p>
                    </div>
                `;
            
            const data = {};
            if (categoryId && categoryId !== 'all') {
                data.category_id = categoryId;
            }
            if (searchTerm) {
                data.search = searchTerm;
            }

            // Hacemos la llamada a la API que ya tienes en PosApiController
            ajaxRequest('{{ route("api.b2b.products.search") }}', 'GET', data)
                .done(function(products) {
                    allProducts = products;
                    renderProducts(products);
                })
                .fail(function() {
                    productsGrid.innerHTML = `
                            <div class="col-span-full flex flex-col items-center justify-center py-16 px-4">
                                <div class="w-24 h-24 bg-red-50 rounded-full flex items-center justify-center mb-6">
                                    <svg class="w-12 h-12 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                
                                <h3 class="text-xl font-bold text-gray-900 mb-2">Error al cargar productos</h3>
                                <p class="text-gray-600 mb-6 text-center max-w-md">
                                    No pudimos cargar el catálogo. Por favor, verifica tu conexión e intenta nuevamente.
                                </p>
                                
                                <button onclick="searchProducts()" class="px-6 py-3 bg-gradient-to-br from-[#0f4db3] to-[#028dff] text-white font-semibold rounded-lg transition-all duration-300 hover:-translate-y-0.5 hover:shadow-lg flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                    Reintentar
                                </button>
                            </div>
                    `;
                });
        }

        // Función para manejar el clic en los filtros de categoría
        function setupFilterTabs() {
            const filterTabs = document.querySelectorAll('.filter-tab');
            
            filterTabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    // Actualizar estilos de tabs
                    filterTabs.forEach(t => {
                        t.classList.remove('active', 'border-[#0f4db3]', 'bg-gradient-to-br', 'from-[#0f4db3]', 'to-[#028dff]', 'text-white', 'shadow-md');
                        t.classList.add('border-gray-200', 'bg-white', 'text-gray-700');
                    });
                    this.classList.remove('border-gray-200', 'bg-white', 'text-gray-700');
                    this.classList.add('active', 'border-[#0f4db3]', 'bg-gradient-to-br', 'from-[#0f4db3]', 'to-[#028dff]', 'text-white', 'shadow-md');
                    
                    // Cargar productos de la categoría
                    const categoryId = this.dataset.categoryId;
                    loadProducts(categoryId);
                });
            });
        }

        // Búsqueda en tiempo real
        const searchInput = document.getElementById('searchInput');
        const clearSearchBtn = document.getElementById('clearSearch');
        let searchTimeout;

        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.trim();
            
            // Mostrar/ocultar botón limpiar
            if (searchTerm) {
                clearSearchBtn.classList.remove('hidden');
            } else {
                clearSearchBtn.classList.add('hidden');
            }
            
            // Debounce para no hacer búsquedas en cada tecla
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                if (searchTerm.length >= 2 || searchTerm.length === 0) {
                    // Filtrar productos localmente
                    const filtered = allProducts.filter(product => {
                        const searchLower = searchTerm.toLowerCase();
                        return product.name?.toLowerCase().includes(searchLower) ||
                               product.description?.toLowerCase().includes(searchLower) ||
                               product.laboratory?.name?.toLowerCase().includes(searchLower) ||
                               product.molecule?.name?.toLowerCase().includes(searchLower) ||
                               product.category?.name?.toLowerCase().includes(searchLower);
                    });
                    renderProducts(filtered);
                }
            }, 300); // Esperar 300ms después de que el usuario deje de escribir
        });

        // Limpiar búsqueda
        clearSearchBtn.addEventListener('click', function() {
            searchInput.value = '';
            this.classList.add('hidden');
            renderProducts(allProducts);
        });

        // Renderizado inicial
        setupFilterTabs();
        loadProducts('all');
    });



    let currentProduct = null; // Variable global para el producto actual

    function openProductModal(productId) {
        const product = allProducts.find(p => p.id === productId);
        if (!product) return;
        
        // Console.log para ver toda la información del producto
        console.log('=== INFORMACIÓN COMPLETA DEL PRODUCTO ===');
        console.log('Product ID:', productId);
        console.log('Datos completos:', product);
        console.log('Lotes disponibles:', product.lots);
        console.log('Laboratorio:', product.laboratory);
        console.log('Molécula:', product.molecule);
        console.log('Forma farmacéutica:', product.pharmaceutical_form);
        console.log('Stock en bodega:', product.stock_in_location);
        console.log('=========================================');
        
        currentProduct = product; // Guardar para usar en addToCartFromModal

        // Información básica
        $('#modalTitle').text('Detalles del Producto');
        $('#modalProductName').text(product.name || 'Sin nombre');
        $('#modalDescription').text(product.description || 'Sin descripción disponible');
        $('#modalPrice').text(`$${parseFloat(product.price || 0).toLocaleString('es-CO')}`);
        $('#modalMainImage').text(product.image || '💊');

        // Información técnica
        $('#modalMolecule').text(product.molecule?.name || '-');
        $('#modalConcentration').text(product.concentration || '-');
        $('#modalPharmForm').text(product.pharmaceutical_form?.name || '-');
        $('#modalUnit').text(product.unit_of_measure?.name || '-');
        $('#modalATC').text(product.atc_code || '-');
        $('#modalInvima').text(product.invima_registration || '-');

        // Laboratorio
        if (product.laboratory?.name) {
            $('#modalLaboratory').find('.text-sm.font-bold').text(product.laboratory.name);
        } else {
            $('#modalLaboratory').find('.text-sm.font-bold').text('No especificado');
        }

        // Stock con barra de progreso
        const stock = parseFloat(product.stock_in_location || 0);
        const stock_minimo = parseFloat(product.stock_minimo || 10);
        let stockText = '';
        let stockColorClass = '';
        let stockPercentage = 0;

        if (stock <= 0) {
            stockText = 'Agotado';
            stockColorClass = 'bg-red-500';
            stockPercentage = 0;
        } else if (stock <= stock_minimo) {
            stockText = `Stock bajo (${stock} unidades)`;
            stockColorClass = 'bg-orange-500';
            stockPercentage = (stock / stock_minimo) * 100;
        } else {
            stockText = `En stock (${stock} unidades)`;
            stockColorClass = 'bg-green-500';
            stockPercentage = Math.min(100, (stock / (stock_minimo * 2)) * 100);
        }

        $('#modalStockText').text(stockText).removeClass('text-green-600 text-orange-600 text-red-600')
            .addClass(stock > stock_minimo ? 'text-green-600' : stock > 0 ? 'text-orange-600' : 'text-red-600');
        $('#modalStockBar').removeClass('bg-green-500 bg-orange-500 bg-red-500').addClass(stockColorClass)
            .css('width', stockPercentage + '%');
        $('#modalStockQuantity').text(`${stock} unidades disponibles`);
        $('#modalStockMin').text(`Mínimo: ${stock_minimo}`);

        // Badges dinámicos
        let badgesHTML = '';
        if (product.controlled) {
            badgesHTML += '<span class="inline-flex items-center gap-1 px-3 py-1 bg-red-500 text-white rounded-full text-xs font-bold shadow-lg">⚠️ Controlado</span>';
        }
        if (product.cold_chain) {
            badgesHTML += '<span class="inline-flex items-center gap-1 px-3 py-1 bg-blue-500 text-white rounded-full text-xs font-bold shadow-lg">❄️ Cadena Frío</span>';
        }
        if (product.regulated) {
            badgesHTML += '<span class="inline-flex items-center gap-1 px-3 py-1 bg-purple-500 text-white rounded-full text-xs font-bold shadow-lg">📋 Regulado</span>';
        }
        $('#modalBadges').html(badgesHTML);

        // Mostrar lotes disponibles
        let lotsHTML = '';
        if (product.lots && product.lots.length > 0) {
            lotsHTML = product.lots.map(lot => {
                const expirationDate = new Date(lot.expiration_date);
                const today = new Date();
                const daysUntilExpiration = Math.floor((expirationDate - today) / (1000 * 60 * 60 * 24));
                
                let expirationColor = 'text-green-600';
                let expirationBg = 'bg-green-50';
                let expirationIcon = '✓';
                
                if (daysUntilExpiration < 30) {
                    expirationColor = 'text-red-600';
                    expirationBg = 'bg-red-50';
                    expirationIcon = '⚠️';
                } else if (daysUntilExpiration < 90) {
                    expirationColor = 'text-orange-600';
                    expirationBg = 'bg-orange-50';
                    expirationIcon = '⏰';
                }
                
                return `
                    <div class="bg-white p-3 rounded-lg border-2 border-purple-100 hover:border-purple-300 transition-colors">
                        <div class="flex justify-between items-start mb-2">
                            <div class="flex-1">
                                <div class="text-xs text-gray-500">Lote</div>
                                <div class="font-bold text-gray-900">${lot.lot_number}</div>
                            </div>
                            <div class="text-right">
                                <div class="text-xs text-gray-500">Cantidad</div>
                                <div class="font-bold text-purple-600">${lot.quantity} und</div>
                            </div>
                        </div>
                        <div class="${expirationBg} ${expirationColor} px-2 py-1 rounded text-xs font-semibold flex items-center gap-1">
                            <span>${expirationIcon}</span>
                            <span>Vence: ${expirationDate.toLocaleDateString('es-CO')}</span>
                        </div>
                    </div>
                `;
            }).join('');
        } else {
            lotsHTML = '<div class="text-center text-gray-500 text-sm py-4">No hay lotes disponibles</div>';
        }
        $('#modalLots').html(lotsHTML);

        // Configurar cantidad
        $('#quantityInput').val(1).attr('max', stock);
        updateModalSubtotal();

        // Mostrar modal
        const modal = $('#productModal');
        modal.removeClass('opacity-0 invisible');
        modal.find('.modal-content').removeClass('scale-75 translate-y-10');
        $('body').css('overflow', 'hidden');
    }

    // Función para CERRAR el modal
    function closeModal() {
        const modal = $('#productModal');
        modal.addClass('opacity-0');
        modal.find('.modal-content').addClass('scale-75 translate-y-10');
        // Esperamos a que la animación termine para ocultarlo completamente
        setTimeout(() => {
            modal.addClass('invisible');
            $('body').css('overflow', 'auto');
        }, 400);
    }

    // Función para abrir lightbox de imagen
    function openImageLightbox() {
        const imageContent = $('#modalMainImage').text();
        $('#lightboxImage').text(imageContent);
        
        const lightbox = $('#imageLightbox');
        lightbox.removeClass('opacity-0 invisible');
        setTimeout(() => {
            $('#lightboxImage').removeClass('scale-75').addClass('scale-100');
        }, 50);
    }

    // Función para cerrar lightbox de imagen
    function closeImageLightbox() {
        $('#lightboxImage').removeClass('scale-100').addClass('scale-75');
        setTimeout(() => {
            $('#imageLightbox').addClass('opacity-0 invisible');
        }, 300);
    }


    // Función para cambiar la cantidad en el modal
    function changeQuantity(delta) {
        const input = $('#quantityInput');
        const currentValue = parseInt(input.val());
        const maxValue = parseInt(input.attr('max'));
        let newValue = currentValue + delta;

        if (newValue < 1) newValue = 1;
        if (newValue > maxValue) newValue = maxValue;

        input.val(newValue);
        updateModalSubtotal();
    }

    // Actualizar subtotal en el modal
    function updateModalSubtotal() {
        if (!currentProduct) return;
        const quantity = parseInt($('#quantityInput').val()) || 1;
        const price = parseFloat(currentProduct.price || 0);
        const subtotal = quantity * price;
        $('#modalSubtotal').text(`$${subtotal.toLocaleString('es-CO')}`);
    }

    // Agregar al carrito desde el modal
    function addToCartFromModal() {
        if (!currentProduct) return;
        const quantity = parseInt($('#quantityInput').val()) || 1;
        
        // Aquí llamarías a tu función de agregar al carrito
        quickAddToCart(currentProduct.id, quantity);
        closeModal();
    }

    
function quickAddToCart(productId, quantity = 1) {
        console.log('dentro de quickAdd', productId)
        const product = allProducts.find(p => p.id === productId);
        if (!product) return;
        const data = {
            product_id: productId,
            quantity: 1, 
            unit_of_measure_id: product.unit_of_measure_id
        };

        // Llamamos a la nueva ruta de la API
        ajaxRequest('{{ route("api.b2b.cart.add") }}', 'POST', data)  
            .done(function(response) {
                if (response.success) {
                    showNotification(response.message, 'success');
                    // Actualizamos el contador del carrito en la cabecera
                    document.getElementById('cartBadge').textContent = response.cart_count;
                }
            });
    }

    function renderCart(cartData) {
        const cartItemsContainer = document.getElementById('cartItems');
        const cartTotalContainer = document.getElementById('cartTotal');
        const cartTitle = document.getElementById('cartTitle');

        if (!cartData || cartData.cart.length === 0) {
            cartItemsContainer.innerHTML = `<div class="text-center py-20 ...">Tu carrito está vacío</div>`;
            cartTotalContainer.innerHTML = '';
            cartTitle.textContent = 'Mi Carrito';
            updateCartBadge(0);
            return;
        }
        
        // Renderizar items
        cartItemsContainer.innerHTML = cartData.cart.map(item => {
            const cartKey = `${item.product_id}_${item.unit_of_measure_id}`;
            return `
                <div class="flex gap-4 py-5 border-b border-indigo-500/10">
                    <div class="w-20 h-20 ...">${item.image ?? '📦'}</div>
                    <div class="flex-1">
                        <p class="font-bold ...">${item.name}</p>
                        <p class="text-indigo-500 ...">$${parseFloat(item.price).toLocaleString('es-CO')} c/u</p>
                        <div class="flex items-center gap-3">
                            <button onclick="updateCartQuantity('${cartKey}', -1)" class="...">-</button>
                            <span class="px-4 font-bold">${item.quantity}</span>
                            <button onclick="updateCartQuantity('${cartKey}', 1)" class="...">+</button>
                            <button onclick="removeFromCart('${cartKey}')" class="...">🗑️</button>
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        // Renderizar totales
        const summary = cartData.summary;
        cartTotalContainer.innerHTML = `
            <div class="p-4 bg-white rounded-lg ...">
                <div class="flex justify-between ..."><span>Subtotal:</span><span>$${summary.subtotal.toLocaleString('es-CO')}</span></div>
                <div class="flex justify-between ..."><span>IVA:</span><span>$${summary.tax.toLocaleString('es-CO')}</span></div>
            </div>
            <div class="flex justify-between text-2xl ...">
                <span>Total Final:</span>
                <span class="text-[#0f4db3]">$${summary.total.toLocaleString('es-CO')}</span>
            </div>
            <button class="w-full py-4 ...">Procesar Pedido</button>
        `;

        // Actualizar título y contador
        cartTitle.textContent = `Mi Carrito (${summary.item_count})`;
        updateCartBadge(summary.item_count);
    }

    // <<< AÑADE ESTA NUEVA FUNCIÓN >>>
    function loadCart() {
        ajaxRequest('{{ route("api.b2b.cart.get") }}', 'GET')
            .done(function(response) {
                renderCart(response);
            })
            .fail(function() {
                showAlert('Error', 'No se pudo cargar el carrito.', 'error');
            });
    }

    // <<< MODIFICA TU FUNCIÓN PARA ABRIR EL CARRITO >>>
    function toggleCart() {
        const cartPanel = document.getElementById('cartPanel');
        const overlay = document.getElementById('sidebarOverlay');
        
        // Si estamos abriendo el carrito, cargamos los datos
        if (cartPanel.classList.contains('translate-x-full')) {
            loadCart();
        }

        cartPanel.classList.toggle('translate-x-full');
        overlay.classList.toggle('opacity-0');
        overlay.classList.toggle('invisible');
    }


    



    // (Opcional) Cierra el modal al presionar la tecla Escape
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape' && !$('#productModal').hasClass('invisible')) {
            closeModal();
        }
    });
</script>
@endpush