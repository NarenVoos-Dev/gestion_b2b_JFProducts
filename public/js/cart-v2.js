// ============================================
// CARRITO B2B - FUNCIONES GLOBALES
// ============================================

// Función para abrir/cerrar el carrito
function toggleCart() {

    console.log('Abrir/Cerrar carrito');
    const cartPanel = document.getElementById('cartPanel');
    const overlay = document.getElementById('cartOverlay');
    
    if (!cartPanel || !overlay) {
        console.error('Cart panel or overlay not found');
        return;
    }
    
    const isOpen = !cartPanel.classList.contains('translate-x-full');
    
    if (isOpen) {
        closeCart();
    } else {
        cartPanel.classList.remove('translate-x-full');
        overlay.classList.remove('opacity-0');
        overlay.classList.remove('invisible');
        loadCart();
    }
}

// Función para cerrar el carrito
function closeCart() {
    const cartPanel = document.getElementById('cartPanel');
    const overlay = document.getElementById('cartOverlay');
    if (cartPanel && overlay) {
        cartPanel.classList.add('translate-x-full');
        overlay.classList.add('opacity-0');
        overlay.classList.add('invisible');
    }
}

// Función para cargar el carrito desde el servidor
function loadCart() {
    showCartSkeleton();
    
    apiRequest('/api/b2b/cart', 'GET')
        .then(response => {
            if (response.success) {
                renderCart(response);
            }
        })
        .catch(error => {
            console.error('Error loading cart:', error);
            showNotification('Error al cargar el carrito', 'error');
        });
}

// Mostrar skeleton loader
function showCartSkeleton() {
    const cartItems = document.getElementById('cartItems');
    if (!cartItems) return;
    
    cartItems.innerHTML = `
        <div class="space-y-4 animate-pulse">
            <div class="bg-gray-200 rounded-xl h-24"></div>
            <div class="bg-gray-200 rounded-xl h-24"></div>
            <div class="bg-gray-200 rounded-xl h-24"></div>
        </div>
    `;
}

// Renderizar el carrito con los datos del servidor
function renderCart(cartData) {
    console.log('Rdenriza carrito', cartData)
    const cartItems = document.getElementById('cartItems');
    const cartFooter = document.getElementById('cartFooter');
    
    if (!cartItems || !cartFooter) return;
    
    if (!cartData || cartData.cart.length === 0) {
        cartFooter.style.display = 'none';
        cartItems.innerHTML = `
            <div class="flex flex-col items-center justify-center h-full text-center">
                <svg class="w-24 h-24 text-gray-300 mb-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <circle cx="9" cy="21" r="1"></circle>
                    <circle cx="20" cy="21" r="1"></circle>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                </svg>
                <p class="text-gray-500 text-lg font-semibold">Tu carrito está vacío</p>
                <p class="text-gray-400 text-sm mt-2">Agrega productos para comenzar tu pedido</p>
            </div>
        `;
        return;
    }
    
    cartFooter.style.display = 'block';
    
    let html = '<div class="space-y-4">';
    console.log('=== RENDERIZANDO CARRITO ===');
    console.log('Total items:', cartData.cart.length);
    cartData.cart.forEach(item => {
        console.log('Item:', item.name);
        console.log('  - image_url:', item.image_url);
        console.log('  - image:', item.image);
        console.log('  - laboratory:', item.laboratory);
        // Calcular IVA del item si aplica
        const itemTax = item.has_tax ? item.tax : 0;
        const taxRate = item.tax_rate || 0;
        const taxBadge = item.has_tax 
            ? `<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                 IVA ${taxRate}%: $${parseFloat(itemTax).toLocaleString('es-CO', {minimumFractionDigits: 0})}
               </span>`
            : '';
        
        html += `
            <div data-cart-item-id="${item.id}" class="bg-white rounded-xl p-4 shadow-sm border-2 border-gray-100 hover:border-[#0f4db3]/30 transition-all">
                <div class="flex gap-4">
                    <div class="w-16 h-16 bg-gradient-to-br from-gray-50 to-gray-100 rounded-lg flex items-center justify-center flex-shrink-0 overflow-hidden">
                        <img src="${item.image_url || '/img/no-image.png'}" 
                             alt="${item.name}" 
                             class="w-full h-full object-contain p-1"
                             onerror="this.src='/img/no-image.png'">
                    </div>
                    <div class="flex-1 min-w-0">
                        <h4 class="font-bold text-gray-900 text-sm truncate">${item.name}</h4>
                        <p class="text-xs text-gray-500">${item.laboratory}</p>
                        <div class="flex items-center gap-2 mt-1">
                            <p class="text-lg font-black text-[#0f4db3]">$${parseFloat(item.price).toLocaleString('es-CO')}</p>
                            ${taxBadge}
                        </div>
                    </div>
                    <button onclick="removeFromCart(${item.id})" class="text-red-500 hover:text-red-700 hover:bg-red-50 w-8 h-8 rounded-lg flex items-center justify-center transition-all flex-shrink-0">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/>
                        </svg>
                    </button>
                </div>
                <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-100">
                    <div class="flex items-center bg-gray-100 rounded-lg overflow-hidden">
                        <button onclick="updateQuantity(${item.id}, ${item.quantity - 1})" class="px-3 py-1 text-lg font-bold text-[#0f4db3] hover:bg-[#0f4db3]/10 transition-colors">−</button>
                        <input 
                            type="number" 
                            value="${item.quantity}" 
                            min="1" 
                            class="w-16 px-2 py-1 font-bold text-gray-900 text-center bg-transparent border-none focus:outline-none item-quantity-input"
                            data-cart-item-id="${item.id}"
                            onchange="handleQuantityChange(${item.id}, this.value)"
                            onkeyup="handleQuantityChange(${item.id}, this.value)"
                        />
                        <button onclick="updateQuantity(${item.id}, ${item.quantity + 1})" class="px-3 py-1 text-lg font-bold text-[#0f4db3] hover:bg-[#0f4db3]/10 transition-colors">+</button>
                    </div>
                    <div class="text-right">
                        <div class="text-xs text-gray-500">Subtotal</div>
                        <div class="font-bold text-gray-900 item-subtotal">$${parseFloat(item.subtotal).toLocaleString('es-CO')}</div>
                    </div>
                </div>
            </div>
        `;
    });
    html += '</div>';
    
    cartItems.innerHTML = html;
    updateCartTotals(cartData.summary);
}

// Manejar cambios de cantidad desde el input (con debounce)
let quantityChangeTimeout = null;
function handleQuantityChange(cartItemId, newValue) {
    // Limpiar timeout anterior
    if (quantityChangeTimeout) {
        clearTimeout(quantityChangeTimeout);
    }
    
    // Validar que sea un número válido
    const quantity = parseInt(newValue);
    if (isNaN(quantity) || quantity < 1) {
        return;
    }
    
    // Esperar 500ms después de que el usuario deje de escribir
    quantityChangeTimeout = setTimeout(() => {
        updateQuantity(cartItemId, quantity);
    }, 500);
}

// Actualizar cantidad con optimistic update mejorado
function updateQuantity(cartItemId, newQuantity) {
    if (newQuantity < 1) {
        removeFromCart(cartItemId);
        return;
    }
    
    // Actualizar UI inmediatamente (optimista)
    const itemElement = document.querySelector(`[data-cart-item-id="${cartItemId}"]`);
    if (!itemElement) return;
    
    const quantityInput = itemElement.querySelector('.item-quantity-input');
    const oldQuantity = parseInt(quantityInput ? quantityInput.value : newQuantity);
    
    // Obtener precio unitario del item
    const priceText = itemElement.querySelector('.text-\\[\\#0f4db3\\]').textContent;
    // Remover $ y puntos de miles, convertir comas a puntos decimales
    const price = parseFloat(priceText.replace(/\$/g, '').replace(/\./g, '').replace(/,/g, '.'));
    
    // Calcular nuevo subtotal
    const newSubtotal = price * newQuantity;
    
    // Actualizar cantidad en UI
    if (quantityInput) quantityInput.value = newQuantity;
    
    // Actualizar subtotal en UI
    const subtotalElement = itemElement.querySelector('.item-subtotal');
    if (subtotalElement) {
        subtotalElement.textContent = '$' + newSubtotal.toLocaleString('es-CO', {minimumFractionDigits: 0, maximumFractionDigits: 0});
    }
    
    // Recalcular totales de forma optimista
    updateCartTotalsOptimistic();
    
    // Luego hacer la petición al servidor en background
    apiRequest('/api/b2b/cart/update', 'POST', {
        cart_item_id: cartItemId,
        quantity: newQuantity
    })
    .then(response => {
        if (response.success) {
            // Actualizar con valores reales del servidor
            loadCart();
        }
    })
    .catch(error => {
        // Rollback: restaurar cantidad anterior
        if (quantityInput) quantityInput.value = oldQuantity;
        loadCart(); // Recargar para restaurar estado correcto
        showNotification(error.message || 'Error al actualizar cantidad', 'error');
    });
}

// Función para recalcular totales de forma optimista
function updateCartTotalsOptimistic() {
    let subtotal = 0;
    let tax = 0;
    
    // Recorrer todos los items visibles en el carrito
    document.querySelectorAll('[data-cart-item-id]').forEach(itemElement => {
        const quantityInput = itemElement.querySelector('.item-quantity-input');
        const quantity = parseInt(quantityInput ? quantityInput.value : 0);
        
        const priceText = itemElement.querySelector('.text-\\[\\#0f4db3\\]').textContent;
        const price = parseFloat(priceText.replace(/\$/g, '').replace(/\./g, '').replace(/,/g, '.'));
        
        const itemSubtotal = price * quantity;
        subtotal += itemSubtotal;
        
        // Verificar si tiene IVA
        const taxBadge = itemElement.querySelector('.bg-green-100');
        if (taxBadge) {
            // Extraer porcentaje de IVA del badge
            const taxText = taxBadge.textContent;
            const taxRateMatch = taxText.match(/IVA\s+(\d+)%/);
            if (taxRateMatch) {
                const taxRate = parseFloat(taxRateMatch[1]);
                const itemTax = itemSubtotal * (taxRate / 100);
                tax += itemTax;
                
                // Actualizar el monto de IVA en el badge
                taxBadge.innerHTML = `IVA ${taxRate}%: $${itemTax.toLocaleString('es-CO', {minimumFractionDigits: 0})}`;
            }
        }
    });
    
    const total = subtotal + tax;
    
    // Actualizar totales en el footer
    updateCartTotals({
        subtotal: subtotal,
        tax: tax,
        total: total
    });
}

// Eliminar del carrito con optimistic update mejorado
function removeFromCart(cartItemId) {
    // Obtener badge actual
    const badge = document.getElementById('cartBadge');
    const currentCount = parseInt(badge.textContent) || 0;
    
    // Actualizar badge inmediatamente
    updateCartBadge(Math.max(0, currentCount - 1));
    
    // Eliminar visualmente el item del DOM
    const itemElement = document.querySelector(`[data-cart-item-id="${cartItemId}"]`);
    if (itemElement) {
        // Guardar referencia para rollback
        const parentElement = itemElement.parentElement;
        const nextSibling = itemElement.nextSibling;
        const itemClone = itemElement.cloneNode(true);
        
        // Animar salida y eliminar
        itemElement.style.transition = 'all 0.3s ease';
        itemElement.style.opacity = '0';
        itemElement.style.transform = 'translateX(-20px)';
        
        setTimeout(() => {
            itemElement.remove();
            
            // Recalcular totales después de eliminar
            updateCartTotalsOptimistic();
            
            // Verificar si el carrito quedó vacío
            const remainingItems = document.querySelectorAll('[data-cart-item-id]');
            if (remainingItems.length === 0) {
                // Mostrar mensaje de carrito vacío
                const cartItems = document.getElementById('cartItems');
                const cartFooter = document.getElementById('cartFooter');
                cartFooter.style.display = 'none';
                cartItems.innerHTML = `
                    <div class="flex flex-col items-center justify-center h-full text-center">
                        <svg class="w-24 h-24 text-gray-300 mb-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <circle cx="9" cy="21" r="1"></circle>
                            <circle cx="20" cy="21" r="1"></circle>
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                        </svg>
                        <p class="text-gray-500 text-lg font-semibold">Tu carrito está vacío</p>
                        <p class="text-gray-400 text-sm mt-2">Agrega productos para comenzar tu pedido</p>
                    </div>
                `;
            }
        }, 300);
        
        // Hacer petición al servidor en background
        apiRequest(`/api/b2b/cart/remove/${cartItemId}`, 'DELETE')
        .then(response => {
            if (response.success) {
                showNotification(response.message, 'info');
                updateCartBadge(response.cart_count);
                // Actualizar con datos reales del servidor
                loadCart();
            }
        })
        .catch(error => {
            // Rollback en caso de error: restaurar el item
            if (nextSibling) {
                parentElement.insertBefore(itemClone, nextSibling);
            } else {
                parentElement.appendChild(itemClone);
            }
            updateCartBadge(currentCount);
            updateCartTotalsOptimistic();
            showNotification('Error al eliminar producto', 'error');
        });
    }
}

// Actualizar totales en el footer
function updateCartTotals(summary) {
    const subtotalElement = document.getElementById('cartSubtotal');
    const taxElement = document.getElementById('cartTax');
    const totalElement = document.getElementById('cartTotal');
    
    if (subtotalElement) subtotalElement.textContent = '$' + parseFloat(summary.subtotal).toLocaleString('es-CO');
    if (taxElement) taxElement.textContent = '$' + parseFloat(summary.tax).toLocaleString('es-CO');
    if (totalElement) totalElement.textContent = '$' + parseFloat(summary.total).toLocaleString('es-CO');
}

// Función helper para hacer peticiones API
function apiRequest(url, method = 'GET', data = null) {
    const options = {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        credentials: 'same-origin'
    };
    
    if (data && method !== 'GET') {
        options.body = JSON.stringify(data);
    }
    
    return fetch(url, options)
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => {
                    throw new Error(err.message || 'Error en la petición');
                });
            }
            return response.json();
        });
}
