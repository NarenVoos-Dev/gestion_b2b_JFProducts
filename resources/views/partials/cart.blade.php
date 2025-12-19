<div id="cartPanel" class="fixed right-0 top-0 w-full md:w-[450px] h-full bg-white shadow-2xl translate-x-full transition-transform duration-400 z-[300] flex flex-col">
    
    <!-- Header del Carrito -->
    <div class="p-4 border-b-2 border-[#0f4db3]/20 flex justify-between items-center bg-gradient-to-r from-[#0f4db3] to-[#028dff] flex-shrink-0">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="9" cy="21" r="1"></circle>
                <circle cx="20" cy="21" r="1"></circle>
                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
            </svg>
            <h3 class="text-xl font-extrabold text-white">Mi Carrito</h3>
        </div>
        <button onclick="closeCart()" class="bg-white/20 hover:bg-white/30 backdrop-blur-md border-none w-8 h-8 rounded-full cursor-pointer text-white text-xl transition-all duration-300 flex items-center justify-center hover:scale-110">
            ×
        </button>
    </div>
    
    <!-- Items del Carrito -->
    <div id="cartItems" class="flex-1 p-6 overflow-y-auto bg-gray-50">
        <!-- Los items se cargarán dinámicamente aquí -->
        <div id="emptyCart" class="flex flex-col items-center justify-center h-full text-center">
            <svg class="w-24 h-24 text-gray-300 mb-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <circle cx="9" cy="21" r="1"></circle>
                <circle cx="20" cy="21" r="1"></circle>
                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
            </svg>
            <p class="text-gray-500 text-lg font-semibold">Tu carrito está vacío</p>
            <p class="text-gray-400 text-sm mt-2">Agrega productos para comenzar tu pedido</p>
        </div>
    </div>
    
    <!-- Footer con Total y Botón de Checkout -->
    <div id="cartFooter" class="border-t-2 border-[#0f4db3]/20 bg-white flex-shrink-0" style="display: none;">
        <!-- Resumen de Totales -->
        <div class="p-6 space-y-3">
            <div class="flex justify-between text-sm">
                <span class="text-gray-600">Subtotal:</span>
                <span id="cartSubtotal" class="font-semibold text-gray-900">$0</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-600">IVA (19%):</span>
                <span id="cartTax" class="font-semibold text-gray-900">$0</span>
            </div>
            <div class="h-px bg-gray-200"></div>
            <div class="flex justify-between text-lg">
                <span class="font-bold text-gray-900">Total:</span>
                <span id="cartTotal" class="font-black text-2xl bg-gradient-to-r from-[#0f4db3] to-[#028dff] bg-clip-text text-transparent">$0</span>
            </div>
        </div>
        
        <!-- Botón de Checkout -->
        <div class="p-6 pt-0">
            <button onclick="proceedToCheckout()" class="w-full py-4 text-lg font-bold rounded-xl bg-gradient-to-br from-[#0f4db3] to-[#028dff] text-white border-none cursor-pointer transition-all duration-300 flex items-center justify-center gap-3 hover:shadow-2xl hover:shadow-[#0f4db3]/40 hover:scale-105">
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M5 12h14M12 5l7 7-7 7"/>
                </svg>
                Proceder al Pedido
            </button>
        </div>
    </div>
</div>

<!-- Overlay para cerrar el carrito al hacer clic fuera -->
<div id="cartOverlay" class="fixed inset-0 bg-black/50 backdrop-blur-sm opacity-0 invisible transition-all duration-400 z-[299]" onclick="closeCart()"></div>