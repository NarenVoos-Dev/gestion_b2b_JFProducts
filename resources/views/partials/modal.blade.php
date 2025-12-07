<div id="productModal" class="fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center z-[1000] opacity-0 invisible transition-all duration-400 p-4">
    <div class="modal-content bg-white rounded-2xl max-w-7xl w-full max-h-[90vh] overflow-hidden scale-75 translate-y-10 transition-all duration-400 shadow-2xl flex flex-col">
        
        <!-- Header con botón cerrar -->
        <div class="bg-gradient-to-r from-[#0f4db3] to-[#028dff] p-4 flex justify-between items-center flex-shrink-0">
            <h2 id="modalTitle" class="text-2xl font-extrabold text-white">Detalles del Producto</h2>
            <button onclick="closeModal()" class="bg-white/20 hover:bg-white/30 backdrop-blur-md border-none w-10 h-10 rounded-full cursor-pointer text-white text-2xl transition-all duration-300 flex items-center justify-center hover:scale-110">
                ×
            </button>
        </div>
        
        <!-- Contenido principal - SIN SCROLL -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 p-6 flex-1 overflow-hidden">
            
            <!-- COLUMNA IZQUIERDA: IMAGEN (1/3) -->
            <div class="space-y-4 flex flex-col">
                <!-- Imagen principal -->
                <div class="relative flex-shrink-0">
                    <div id="modalMainImage" 
                         onclick="openImageLightbox()" 
                         class="w-full h-64 bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl flex items-center justify-center shadow-lg border-4 border-gray-100 cursor-pointer hover:border-[#0f4db3] transition-all duration-300 hover:scale-105 overflow-hidden">
                        <img id="modalProductImage" 
                             src="/img/no-image.png" 
                             alt="Producto" 
                             class="w-full h-full object-contain p-4"
                             onerror="this.src='/img/no-image.png'">
                    </div>
                    <div class="absolute bottom-2 right-2 bg-black/50 text-white px-2 py-1 rounded text-xs">
                        🔍 Clic para ampliar
                    </div>
                    <!-- Badges sobre la imagen -->
                    <div id="modalBadges" class="absolute top-4 left-4 flex flex-col gap-2"></div>
                </div>
                
                <!-- Stock visual -->
                <div class="bg-gray-50 p-4 rounded-xl flex-shrink-0">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm font-semibold text-gray-700">Disponibilidad</span>
                        <span id="modalStockText" class="text-sm font-bold text-green-600">En stock</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                        <div id="modalStockBar" class="bg-green-500 h-full rounded-full transition-all duration-500" style="width: 75%"></div>
                    </div>
                    <div class="flex justify-between items-center mt-2 text-xs text-gray-500">
                        <span id="modalStockQuantity">0 unidades</span>
                        <span id="modalStockMin">Mínimo: 0</span>
                    </div>
                </div>

                <!-- Laboratorio -->
                <div id="modalLaboratory" class="bg-blue-50 p-4 rounded-xl border-2 border-blue-100 flex-shrink-0">
                    <div class="flex items-center gap-3">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        <div>
                            <div class="text-xs text-gray-600 font-medium">Laboratorio</div>
                            <div class="text-sm font-bold text-gray-900">Cargando...</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- COLUMNA CENTRAL: INFORMACIÓN (1/3) -->
            <div class="space-y-4 flex flex-col overflow-hidden">
                <!-- Nombre del producto -->
                <div class="flex-shrink-0">
                    <h3 id="modalProductName" class="text-2xl font-extrabold text-gray-900 mb-2 leading-tight">
                        Nombre del Producto
                    </h3>
                    <p id="modalDescription" class="text-gray-600 text-sm leading-relaxed line-clamp-2">
                        Descripción del producto...
                    </p>
                </div>

                <!-- Precio -->
                <div class="bg-gradient-to-br from-[#0f4db3]/10 to-[#028dff]/10 p-4 rounded-xl border-2 border-[#0f4db3]/20 text-center flex-shrink-0">
                    <div class="text-xs text-gray-600 font-medium mb-1">Precio por unidad</div>
                    <div id="modalPrice" class="text-4xl font-black bg-gradient-to-r from-[#0f4db3] to-[#028dff] bg-clip-text text-transparent">
                        $0
                    </div>
                    <div class="text-xs text-gray-500 mt-1">IVA incluido</div>
                </div>

                <!-- Información técnica -->
                <div class="bg-gray-50 rounded-xl p-4 flex-1 overflow-y-auto">
                    <div class="text-sm font-bold text-gray-900 mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5 text-[#0f4db3]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Información Técnica
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div class="bg-white p-2 rounded-lg">
                            <div class="text-gray-500 mb-1">Principio Activo</div>
                            <div id="modalMolecule" class="font-bold text-gray-900">-</div>
                        </div>
                        <div class="bg-white p-2 rounded-lg">
                            <div class="text-gray-500 mb-1">Concentración</div>
                            <div id="modalConcentration" class="font-bold text-gray-900">-</div>
                        </div>
                        <div class="bg-white p-2 rounded-lg">
                            <div class="text-gray-500 mb-1">Forma Farmacéutica</div>
                            <div id="modalPharmForm" class="font-bold text-gray-900">-</div>
                        </div>
                        <div class="bg-white p-2 rounded-lg">
                            <div class="text-gray-500 mb-1">Unidad</div>
                            <div id="modalUnit" class="font-bold text-gray-900">-</div>
                        </div>
                        <div class="bg-white p-2 rounded-lg">
                            <div class="text-gray-500 mb-1">Código ATC</div>
                            <div id="modalATC" class="font-bold text-gray-900">-</div>
                        </div>
                        <div class="bg-white p-2 rounded-lg">
                            <div class="text-gray-500 mb-1">Reg. INVIMA</div>
                            <div id="modalInvima" class="font-bold text-gray-900 text-xs">-</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- COLUMNA DERECHA: LOTES Y ACCIONES (1/3) -->
            <div class="space-y-4 flex flex-col overflow-hidden">
                <!-- Lotes disponibles -->
                <div class="bg-purple-50 rounded-xl p-4 border-2 border-purple-100 flex-1 overflow-hidden flex flex-col">
                    <div class="text-sm font-bold text-gray-900 mb-3 flex items-center gap-2 flex-shrink-0">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        Lotes Disponibles
                    </div>
                    
                    <!-- Tabla de lotes -->
                    <div class="flex-1 overflow-hidden flex flex-col">
                        <div class="overflow-y-auto flex-1">
                            <table class="w-full text-xs">
                                <thead class="bg-purple-100 sticky top-0">
                                    <tr>
                                        <th class="px-2 py-2 text-left text-purple-900">Sel.</th>
                                        <th class="px-2 py-2 text-left text-purple-900">Lote</th>
                                        <th class="px-2 py-2 text-left text-purple-900">Vence</th>
                                        <th class="px-2 py-2 text-right text-purple-900">Stock</th>
                                        <th class="px-2 py-2 text-center text-purple-900">Estado</th>
                                    </tr>
                                </thead>
                                <tbody id="lotsTableBody">
                                    <tr>
                                        <td colspan="5" class="text-center text-gray-500 py-4">Cargando lotes...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Paginación -->
                        <div id="lotsPagination" class="flex justify-between items-center mt-2 pt-2 border-t border-purple-200 flex-shrink-0">
                            <button id="prevLotsPage" class="px-2 py-1 text-xs bg-purple-200 text-purple-900 rounded hover:bg-purple-300 disabled:opacity-50 disabled:cursor-not-allowed">
                                ← Anterior
                            </button>
                            <span id="lotsPageInfo" class="text-xs text-purple-900 font-medium">Página 1 de 1</span>
                            <button id="nextLotsPage" class="px-2 py-1 text-xs bg-purple-200 text-purple-900 rounded hover:bg-purple-300 disabled:opacity-50 disabled:cursor-not-allowed">
                                Siguiente →
                            </button>
                        </div>
                    </div>
                    
                    <p class="text-xs text-purple-700 mt-2 flex-shrink-0">
                        💡 Si no seleccionas un lote, el administrador asignará uno al procesar tu pedido
                    </p>
                </div>

                <!-- Selector de cantidad -->
                <div class="bg-white border-2 border-gray-200 rounded-xl p-4 flex-shrink-0">
                    <div class="flex items-center justify-between mb-3">
                        <span class="font-bold text-gray-900 text-sm">Cantidad:</span>
                        <div class="flex items-center bg-gray-100 rounded-lg overflow-hidden">
                            <button onclick="changeQuantity(-1)" class="px-3 py-2 text-xl font-bold text-[#0f4db3] hover:bg-[#0f4db3]/10 transition-colors">
                                −
                            </button>
                            <input type="number" id="quantityInput" value="1" min="1" max="100" 
                                   class="w-16 text-center font-bold text-lg border-none bg-transparent focus:outline-none"
                                   onchange="updateModalSubtotal()"
                                   onkeyup="updateModalSubtotal()">
                            <button onclick="changeQuantity(1)" class="px-3 py-2 text-xl font-bold text-[#0f4db3] hover:bg-[#0f4db3]/10 transition-colors">
                                +
                            </button>
                        </div>
                    </div>
                    <div class="text-xs text-gray-500 text-center">
                        Subtotal: <span id="modalSubtotal" class="font-bold text-gray-900">$0</span>
                    </div>
                </div>

                <!-- Botón de agregar -->
                <button onclick="addToCartFromModal()" 
                        class="w-full py-3 text-base font-bold rounded-xl bg-gradient-to-br from-[#0f4db3] to-[#028dff] text-white border-none cursor-pointer transition-all duration-300 flex items-center justify-center gap-3 hover:shadow-xl hover:shadow-[#0f4db3]/40 hover:scale-105 flex-shrink-0">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="9" cy="21" r="1"></circle>
                        <circle cx="20" cy="21" r="1"></circle>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                    </svg>
                    Agregar al Pedido
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Lightbox para imagen ampliada -->
<div id="imageLightbox" class="fixed inset-0 bg-black/90 backdrop-blur-sm flex items-center justify-center z-[2000] opacity-0 invisible transition-all duration-300">
    <button onclick="closeImageLightbox()" class="absolute top-4 right-4 bg-white/20 hover:bg-white/30 backdrop-blur-md border-none w-12 h-12 rounded-full cursor-pointer text-white text-3xl transition-all duration-300 flex items-center justify-center hover:scale-110 z-10">
        ×
    </button>
    <img id="lightboxImage" 
         src="/img/no-image.png" 
         alt="Producto" 
         class="max-w-[90vw] max-h-[90vh] object-contain transform transition-transform duration-300 scale-75 rounded-lg shadow-2xl"
         onerror="this.src='/img/no-image.png'">
</div>