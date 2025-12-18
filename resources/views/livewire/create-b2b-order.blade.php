<div class="p-6">
    {{-- Encabezado --}}
    <div class="mb-6">
        <h2 class="text-2xl font-bold mb-6">{{ $isEditMode ? 'Editar Pedido B2B' : 'Crear Pedido B2B' }}</h2>
        <p class="text-gray-600">Agrega productos y asigna lotes como en el sistema B2B</p>
    </div>

    {{-- Información del Pedido --}}
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="text-lg font-semibold mb-4">Información del Pedido</h3>
        
        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Cliente *</label>
                <select wire:model="client_id" class="w-full border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    <option value="">Seleccionar cliente...</option>
                    @foreach($this->clients as $client)
                        <option value="{{ $client->id }}">{{ $client->name }}</option>
                    @endforeach
                </select>
                @error('client_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                <select wire:model="status" class="w-full border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    <option value="Pendiente">Pendiente</option>
                    <option value="Separación">Separación</option>
                </select>
            </div>
            
            <div class="col-span-3">
                <label class="block text-sm font-medium text-gray-700 mb-2">Notas</label>
                <textarea wire:model="notes" rows="2" class="w-full border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500"></textarea>
            </div>
        </div>
    </div>

    {{-- Productos del Pedido --}}
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Productos del Pedido</h3>
            <button 
                wire:click="openProductModal" 
                type="button" 
                style="background-color: #16a34a; color: white; padding: 0.5rem 1rem; border-radius: 0.375rem; display: flex; align-items: center; gap: 0.5rem; font-weight: 600; cursor: pointer;"
                onmouseover="this.style.backgroundColor='#15803d'" 
                onmouseout="this.style.backgroundColor='#16a34a'">
                <svg style="width: 1.25rem; height: 1.25rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Agregar Producto
            </button>
        </div>

        @if(empty($items))
            <div class="text-center py-8 text-gray-500">
                <p>No hay productos agregados. Usa el botón "Agregar Producto" para comenzar.</p>
            </div>
        @else
            <table class="w-full border">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-3 text-left">Producto</th>
                        <th class="p-3 text-center">Cantidad</th>
                        <th class="p-3 text-center">Costo</th>
                        <th class="p-3 text-center">% Cliente</th>
                        <th class="p-3 text-center">Precio</th>
                        <th class="p-3 text-center">Lotes</th>
                        <th class="p-3 text-center">Total</th>
                        <th class="p-3 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $index => $item)
                        <tr class="border-b">
                            <td class="p-3">{{ $item['product_name'] }}</td>
                            <td class="p-3 text-center">{{ $item['quantity'] }}</td>
                            <td class="p-3 text-center">${{ number_format($item['base_cost'] ?? 0, 2) }}</td>
                            <td class="p-3 text-center">{{ number_format($item['client_percentage'] ?? 0, 2) }}%</td>
                            <td class="p-3 text-center">${{ number_format($item['price'], 2) }}</td>
                            <td class="p-3 text-center">{{ count($item['lots']) }} lote(s)</td>
                            <td class="p-3 text-center font-bold">${{ number_format($item['quantity'] * $item['price'], 2) }}</td>
                            <td class="p-3 text-center">
                                <button wire:click="removeItem({{ $index }})" type="button" class="text-red-600 hover:text-red-800">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- Totales --}}
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="text-lg font-semibold mb-4">Totales</h3>
        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Subtotal</label>
                <input type="text" value="${{ number_format($subtotal, 2) }}" disabled class="w-full border-gray-300 rounded-md shadow-sm bg-gray-50">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">IVA</label>
                <input type="text" value="${{ number_format($tax, 2) }}" disabled class="w-full border-gray-300 rounded-md shadow-sm bg-gray-50">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Total</label>
                <input type="text" value="${{ number_format($total, 2) }}" disabled class="w-full border-gray-300 rounded-md shadow-sm bg-gray-50 font-bold text-lg">
            </div>
        </div>
    </div>

    {{-- Botones --}}
    <div class="flex justify-end gap-4">
        <a href="{{ route('filament.admin.resources.sales.index') }}" 
           class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded-md font-medium">
            Cancelar
        </a>
        <button wire:click="saveOrder" type="button" 
                style="background-color: #2563eb; color: white; padding: 0.5rem 1.5rem; border-radius: 0.375rem; font-weight: 500; cursor: pointer;"
                onmouseover="this.style.backgroundColor='#1d4ed8'" 
                onmouseout="this.style.backgroundColor='#2563eb'">
            {{ $isEditMode ? 'Actualizar Pedido' : 'Crear Pedido' }}
        </button>
    </div>

    {{-- Modal de Productos --}}
    @if($showProductModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" style="z-index: 9999;">
            <div class="bg-white rounded-lg shadow-xl" style="width: 800px; max-height: 600px; overflow: hidden;">
                {{-- Header del Modal --}}
                <div class="bg-gray-100 px-4 py-3 border-b flex justify-between items-center">
                    <h3 class="text-lg font-semibold">Seleccionar Producto</h3>
                    <button wire:click="$set('showProductModal', false)" type="button" class="text-gray-600 hover:text-gray-800">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                {{-- Contenido del Modal --}}
                <div class="overflow-y-auto" style="max-height: 480px;">
                    {{-- Búsqueda --}}
                    <div class="p-3 bg-gray-50 border-b">
                        <input wire:model.live="productSearch" type="text" placeholder="Buscar producto..." 
                               class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    </div>

                    {{-- Tabla de Productos --}}
                    @if(!$selectedProduct)
                        <table class="w-full text-sm">
                            <thead class="bg-gray-100 sticky top-0">
                                <tr>
                                    <th class="p-2 text-left text-xs font-medium">Producto</th>
                                    <th class="p-2 text-center text-xs font-medium">Costo</th>
                                    <th class="p-2 text-center text-xs font-medium">Stock</th>
                                    <th class="p-2 text-center text-xs font-medium">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($this->products as $product)
                                    <tr class="border-b hover:bg-gray-50">
                                        <td class="p-2">{{ $product->name }}</td>
                                        <td class="p-2 text-center">${{ number_format($this->getBaseCost($product), 2) }}</td>
                                        <td class="p-2 text-center">{{ $product->productLots->where('is_active', true)->sum('quantity') }}</td>
                                        <td class="p-2 text-center">
                                            <button wire:click="selectProduct({{ $product->id }})" type="button"
                                                    style="background-color: #2563eb; color: white; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.75rem; cursor: pointer; font-weight: 500;"
                                                    onmouseover="this.style.backgroundColor='#1d4ed8'" 
                                                    onmouseout="this.style.backgroundColor='#2563eb'">
                                                Seleccionar
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        {{-- Producto Seleccionado con Lotes --}}
                        <div class="p-3">
                            <div class="bg-blue-50 border border-blue-200 rounded p-3 mb-3">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <h4 class="font-semibold">{{ $selectedProduct->name }}</h4>
                                        <p class="text-xs text-gray-600">Costo: ${{ number_format($this->getBaseCost($selectedProduct), 2) }}</p>
                                    </div>
                                    <button 
                                        wire:click="resetProductSelection" 
                                        type="button" 
                                        style="background-color: transparent; color: #2563eb; font-size: 0.75rem; font-weight: 500; cursor: pointer; padding: 0.25rem 0.5rem; border: none;"
                                        onmouseover="this.style.color='#1d4ed8'" 
                                        onmouseout="this.style.color='#2563eb'">
                                        ← Cambiar
                                    </button>
                                </div>
                            </div>

                            <h5 class="font-semibold text-sm mb-2">Asignar Cantidades:</h5>
                            
                            @if(empty($availableLots))
                                <p class="text-gray-500 text-center py-4 text-sm">No hay lotes disponibles.</p>
                            @else
                                <table class="w-full border text-sm">
                                    <thead class="bg-gray-100">
                                        <tr>
                                            <th class="p-2 text-left text-xs">Lote</th>
                                            <th class="p-2 text-center text-xs">Stock</th>
                                            <th class="p-2 text-center text-xs">Vence</th>
                                            <th class="p-2 text-center text-xs">Cantidad</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($availableLots as $index => $lot)
                                            <tr class="border-b">
                                                <td class="p-2 font-medium">{{ $lot['lot_number'] }}</td>
                                                <td class="p-2 text-center">{{ $lot['quantity'] }}</td>
                                                <td class="p-2 text-center text-xs">{{ $lot['expiration_date'] }}</td>
                                                <td class="p-2 text-center">
                                                    <input wire:model="availableLots.{{ $index }}.selected_quantity" 
                                                           type="number" 
                                                           min="0" 
                                                           max="{{ $lot['quantity'] }}" 
                                                           placeholder="0"
                                                           class="w-20 text-sm border-gray-300 rounded shadow-sm focus:border-primary-500 focus:ring-primary-500 text-center">
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Footer del Modal --}}
                <div class="bg-gray-100 px-4 py-3 border-t flex justify-end gap-3">
                    <button wire:click="$set('showProductModal', false)" type="button" 
                            style="background-color: #d1d5db; color: #1f2937; padding: 0.375rem 0.75rem; border-radius: 0.25rem; font-size: 0.875rem; cursor: pointer;"
                            onmouseover="this.style.backgroundColor='#9ca3af'" 
                            onmouseout="this.style.backgroundColor='#d1d5db'">
                        Cancelar
                    </button>
                    @if($selectedProduct)
                        <button wire:click="addProductToOrder" type="button" 
                                style="background-color: #2563eb; color: white; padding: 0.375rem 0.75rem; border-radius: 0.25rem; font-size: 0.875rem; font-weight: 500; cursor: pointer;"
                                onmouseover="this.style.backgroundColor='#1d4ed8'" 
                                onmouseout="this.style.backgroundColor='#2563eb'">
                            Agregar al Pedido
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
