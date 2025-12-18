<?php

namespace App\Livewire;

use App\Models\Client;
use App\Models\Product;
use App\Models\ProductLot;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleItemLot;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Filament\Notifications\Notification;

class CreateB2BOrder extends Component
{
    // Edit mode
    public $saleId = null;
    public $isEditMode = false;
    
    public $client_id;
    public $notes = '';
    public $status = 'Pendiente';
    
    // Items del pedido (similar al carrito)
    public $items = [];
    
    // Modal de productos
    public $showProductModal = false;
    public $selectedProduct = null;
    public $productSearch = '';
    
    // Lotes del producto seleccionado
    public $availableLots = [];
    public $selectedLots = [];
    
    // Totales
    public $subtotal = 0;
    public $tax = 0;
    public $total = 0;
    
    protected $rules = [
        'client_id' => 'required|exists:clients,id',
        'status' => 'required|in:Pendiente,Separación',
    ];
    
    public function mount($saleId = null)
    {
        $this->saleId = $saleId;
        $this->isEditMode = !is_null($saleId);
        
        if ($this->isEditMode) {
            $this->loadExistingOrder();
        }
        
        $this->calculateTotals();
    }
    
    public function loadExistingOrder()
    {
        $sale = Sale::with(['client', 'items.product', 'items.lots.productLot'])
            ->findOrFail($this->saleId);
        
        // Cargar información del cliente y pedido
        $this->client_id = $sale->client_id;
        $this->status = $sale->status;
        $this->notes = $sale->notes ?? '';
        
        // Cargar items con lotes
        foreach ($sale->items as $saleItem) {
            $lots = [];
            foreach ($saleItem->lots as $saleItemLot) {
                $lots[] = [
                    'id' => $saleItemLot->product_lot_id,
                    'lot_number' => $saleItemLot->lot_number,
                    'quantity' => $saleItemLot->productLot->quantity ?? 0,
                    'expiration_date' => $saleItemLot->expiration_date->format('d/m/Y'),
                    'selected_quantity' => $saleItemLot->quantity,
                ];
            }
            
            $this->items[] = [
                'product_id' => $saleItem->product_id,
                'product_name' => $saleItem->product->name,
                'quantity' => $saleItem->quantity,
                'base_cost' => $this->getBaseCost($saleItem->product),
                'client_percentage' => $this->getClientPercentage(),
                'price' => $saleItem->price,
                'tax_rate' => $saleItem->tax_rate,
                'unit_of_measure_id' => $saleItem->unit_of_measure_id,
                'lots' => $lots,
            ];
        }
    }
    
    public function openProductModal()
    {
        // Validar que haya cliente seleccionado
        if (!$this->client_id) {
            Notification::make()
                ->warning()
                ->title('Cliente Requerido')
                ->body('Debes seleccionar un cliente primero para calcular los precios correctamente')
                ->send();
            return;
        }
        
        $this->showProductModal = true;
        $this->selectedProduct = null;
        $this->availableLots = [];
        $this->selectedLots = [];
    }
    
    public function selectProduct($productId)
    {
        $this->selectedProduct = Product::with('productLots')->find($productId);
        
        if ($this->selectedProduct) {
            // Cargar lotes disponibles
            $this->availableLots = $this->selectedProduct->productLots()
                ->where('is_active', true)
                ->where('quantity', '>', 0)
                ->get()
                ->map(function ($lot) {
                    return [
                        'id' => $lot->id,
                        'lot_number' => $lot->lot_number,
                        'quantity' => $lot->quantity,
                        'expiration_date' => $lot->expiration_date->format('d/m/Y'),
                        'selected_quantity' => 0,
                    ];
                })
                ->toArray();
        }
    }
    
    public function resetProductSelection()
    {
        $this->selectedProduct = null;
        $this->availableLots = [];
    }
    
    public function getPriceForClient($product)
    {
        // Obtener el costo del lote más caro del producto
        $maxLotCost = ProductLot::where('product_id', $product->id)
            ->where('is_active', true)
            ->where('quantity', '>', 0)
            ->max('cost');
        
        // Si no hay lotes con costo, usar precio base del producto
        if (!$maxLotCost || $maxLotCost == 0) {
            $maxLotCost = $product->sale_price ?? $product->price ?? 0;
        }
        
        // Si hay cliente seleccionado, aplicar aumento según su lista
        if ($this->client_id) {
            $client = Client::find($this->client_id);
            if ($client && $client->price_list_id) {
                // Obtener el porcentaje de la lista
                $priceList = \DB::table('price_lists')
                    ->where('id', $client->price_list_id)
                    ->first();
                    
                if ($priceList && isset($priceList->percentage) && $priceList->percentage > 0) {
                    $percentage = $priceList->percentage;
                    
                    // Aplicar fórmula de AUMENTO: costo_lote_mayor / (1 - %/100)
                    // Ejemplo: 100,000 / (1 - 20/100) = 100,000 / 0.8 = 125,000
                    if ($percentage < 100) { // Evitar división por cero
                        return round($maxLotCost / (1 - ($percentage / 100)), 2);
                    }
                }
            }
        }
        
        // Sin lista de precios: retornar costo del lote más caro
        return $maxLotCost;
    }
    
    public function getClientPercentage()
    {
        if ($this->client_id) {
            $client = Client::find($this->client_id);
            if ($client && $client->price_list_id) {
                $priceList = \DB::table('price_lists')
                    ->where('id', $client->price_list_id)
                    ->first();
                    
                if ($priceList && isset($priceList->percentage)) {
                    return $priceList->percentage;
                }
            }
        }
        return 0;
    }
    
    public function getBaseCost($product)
    {
        // Obtener el costo del lote más caro
        $maxLotCost = ProductLot::where('product_id', $product->id)
            ->where('is_active', true)
            ->where('quantity', '>', 0)
            ->max('cost');
        
        if (!$maxLotCost || $maxLotCost == 0) {
            $maxLotCost = $product->sale_price ?? $product->price ?? 0;
        }
        
        return $maxLotCost;
    }
    
    public function addProductToOrder()
    {
        if (!$this->selectedProduct) {
            Notification::make()
                ->danger()
                ->title('Error')
                ->body('Selecciona un producto primero')
                ->send();
            return;
        }
        
        // Validar que haya lotes seleccionados
        $lotsWithQuantity = array_filter($this->availableLots, function ($lot) {
            return $lot['selected_quantity'] > 0;
        });
        
        if (empty($lotsWithQuantity)) {
            Notification::make()
                ->danger()
                ->title('Error')
                ->body('Debes asignar cantidades a los lotes')
                ->send();
            return;
        }
        
        // Validar que las cantidades no excedan el stock disponible
        foreach ($lotsWithQuantity as $lot) {
            if ($lot['selected_quantity'] > $lot['quantity']) {
                Notification::make()
                    ->danger()
                    ->title('Cantidad Excedida')
                    ->body("El lote {$lot['lot_number']} solo tiene {$lot['quantity']} unidades disponibles. Solicitaste {$lot['selected_quantity']}.")
                    ->send();
                return;
            }
        }
        
        // Calcular cantidad total
        $totalQuantity = array_sum(array_column($lotsWithQuantity, 'selected_quantity'));
        
        // Obtener costo base y porcentaje del cliente
        $baseCost = $this->getBaseCost($this->selectedProduct);
        $clientPercentage = $this->getClientPercentage();
        $price = $this->getPriceForClient($this->selectedProduct);
        
        // Agregar item al pedido
        $this->items[] = [
            'product_id' => $this->selectedProduct->id,
            'product_name' => $this->selectedProduct->name,
            'quantity' => $totalQuantity,
            'base_cost' => $baseCost, // Costo del lote mayor
            'client_percentage' => $clientPercentage, // % de la lista del cliente
            'price' => $price, // Precio con aumento aplicado
            'tax_rate' => $this->selectedProduct->tax_rate ?? 0,
            'unit_of_measure_id' => $this->selectedProduct->unit_of_measure_id,
            'lots' => array_values($lotsWithQuantity),
        ];
        
        $this->calculateTotals();
        $this->showProductModal = false;
        
        Notification::make()
            ->success()
            ->title('Producto Agregado')
            ->body("{$this->selectedProduct->name} agregado con {$totalQuantity} unidades")
            ->send();
    }
    
    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
        $this->calculateTotals();
    }
    
    public function calculateTotals()
    {
        $this->subtotal = 0;
        $this->tax = 0;
        
        foreach ($this->items as $item) {
            $itemSubtotal = $item['quantity'] * $item['price'];
            $this->subtotal += $itemSubtotal;
            $this->tax += $itemSubtotal * ($item['tax_rate'] / 100);
        }
        
        $this->total = $this->subtotal + $this->tax;
    }
    
    
    public function saveOrder()
    {
        $this->validate();
        
        if (empty($this->items)) {
            Notification::make()
                ->danger()
                ->title('Error')
                ->body('Debes agregar al menos un producto')
                ->send();
            return;
        }
        
        DB::beginTransaction();
        
        try {
            if ($this->isEditMode) {
                $sale = $this->updateOrder();
            } else {
                $sale = $this->createNewOrder();
            }
            
            DB::commit();
            
            Notification::make()
                ->success()
                ->title($this->isEditMode ? 'Pedido Actualizado' : 'Pedido Creado')
                ->body($this->isEditMode ? 'Pedido actualizado exitosamente' : "Pedido #{$sale->id} creado exitosamente")
                ->send();
            
            // Redirigir a la vista del pedido
            return redirect()->route('filament.admin.resources.sales.view', ['record' => $sale->id]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Notification::make()
                ->danger()
                ->title('Error')
                ->body('Error al guardar el pedido: ' . $e->getMessage())
                ->send();
        }
    }
    
    private function createNewOrder()
    {
        // Crear venta
        $sale = Sale::create([
            'business_id' => auth()->user()->business_id ?? 1,
            'client_id' => $this->client_id,
            'location_id' => 1, // Bodega B2B
            'date' => now(),
            'subtotal' => round($this->subtotal, 2),
            'tax' => round($this->tax, 2),
            'total' => round($this->total, 2),
            'is_cash' => false,
            'status' => $this->status,
            'source' => 'b2b',
            'notes' => $this->notes,
        ]);
        
        $this->createSaleItems($sale);
        
        return $sale;
    }
    
    private function updateOrder()
    {
        $sale = Sale::findOrFail($this->saleId);
        
        // Actualizar venta
        $sale->update([
            'client_id' => $this->client_id,
            'subtotal' => round($this->subtotal, 2),
            'tax' => round($this->tax, 2),
            'total' => round($this->total, 2),
            'status' => $this->status,
            'notes' => $this->notes,
        ]);
        
        // Eliminar items y lotes existentes
        foreach ($sale->items as $item) {
            $item->lots()->delete();
        }
        $sale->items()->delete();
        
        // Crear nuevos items
        $this->createSaleItems($sale);
        
        return $sale;
    }
    
    private function createSaleItems($sale)
    {
        // Crear items y lotes
        foreach ($this->items as $item) {
            $saleItem = SaleItem::create([
                'sale_id' => $sale->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'tax_rate' => $item['tax_rate'],
                'unit_of_measure_id' => $item['unit_of_measure_id'],
            ]);
            
            // Crear lotes
            foreach ($item['lots'] as $lot) {
                if ($lot['selected_quantity'] > 0) {
                    $productLot = ProductLot::find($lot['id']);
                    
                    SaleItemLot::create([
                        'sale_item_id' => $saleItem->id,
                        'product_lot_id' => $lot['id'],
                        'quantity' => $lot['selected_quantity'],
                        'lot_number' => $productLot->lot_number,
                        'expiration_date' => $productLot->expiration_date,
                    ]);
                }
            }
        }
    }
    
    
    public function getClientsProperty()
    {
        return Client::where('is_active', true)->orderBy('name')->get();
    }
    
    public function getProductsProperty()
    {
        return Product::query()
            ->where('is_active', true)
            ->when($this->productSearch, function ($query) {
                $query->where('name', 'like', '%' . $this->productSearch . '%');
            })
            ->with('productLots')
            ->orderBy('name')
            ->limit(50)
            ->get();
    }
    
    public function render()
    {
        return view('livewire.create-b2b-order');
    }
}
