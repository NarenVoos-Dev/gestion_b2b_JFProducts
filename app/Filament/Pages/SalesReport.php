<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use App\Models\Client;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class SalesReport extends Page implements HasForms, HasActions
{
    use InteractsWithForms;
    use InteractsWithActions;
    
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    
    protected static string $view = 'filament.pages.sales-reports';
    
    protected static ?string $navigationGroup = 'Reportes';
    
    protected static ?string $navigationLabel = 'Reportes de Ventas';
    
    protected static ?string $title = 'Reportes de Ventas';
    
    protected static ?int $navigationSort = 100;
    
    // Filtros
    public ?string $date_from = null;
    public ?string $date_to = null;
    public ?int $client_id = null;
    public ?int $product_id = null;
    public ?string $status = null;
    public string $activeTab = 'by_order';
    
    // Modal de exportación
    public bool $showExportModal = false;
    public string $exportType = '';
    public array $selectedColumns = [];
    public array $availableColumns = [];
    
    public function mount(): void
    {
        // Establecer fechas por defecto (último mes)
        $this->date_from = now()->subMonth()->format('Y-m-d');
        $this->date_to = now()->format('Y-m-d');
        $this->status = null; // Por defecto mostrar todos los estados
        
        // Llenar el formulario con los valores iniciales
        $this->form->fill([
            'date_from' => $this->date_from,
            'date_to' => $this->date_to,
            'status' => $this->status,
            'client_id' => $this->client_id,
            'product_id' => $this->product_id,
        ]);
    }
    
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // Fila Superior: Cliente y Producto
                \Filament\Forms\Components\Grid::make(2)
                    ->schema([
                        Select::make('client_id')
                            ->label('Cliente')
                            ->options(Client::where('is_active', true)->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->nullable(),
                            
                        Select::make('product_id')
                            ->label('Producto')
                            ->options(Product::where('is_active', true)->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->nullable(),
                    ]),

                // Fila Inferior: Fechas y Estado
                \Filament\Forms\Components\Grid::make(3)
                    ->schema([
                        DatePicker::make('date_from')
                            ->label('Desde')
                            ->default(now()->subMonth())
                            ->native(false)
                            ->maxDate(now()),
                            
                        DatePicker::make('date_to')
                            ->label('Hasta')
                            ->default(now())
                            ->native(false)
                            ->maxDate(now()),
                            
                        Select::make('status')
                            ->label('Estado')
                            ->options([
                                'Facturado' => 'Facturado',
                                'Finalizado' => 'Finalizado',
                                'Pendiente' => 'Pendiente',
                                'Cancelado' => 'Cancelado',
                            ])
                            ->nullable()
                            ->placeholder('Todos los estados'),
                    ]),
            ])
            ->columns(1);
    }
    
    // Obtener datos para cada reporte
    public function getSalesByOrderData()
    {
        $query = \App\Models\Sale::query()
            ->with(['client'])
            ->whereBetween('date', [$this->date_from, $this->date_to]);
            
        if ($this->status) {
            $query->where('status', $this->status);
        }
            
        if ($this->client_id) {
            $query->where('client_id', $this->client_id);
        }
        
        // El filtro de producto ya no aplica si quitamos los detalles de producto,
        // pero podemos mantenerlo si queremos ver "Pedidos que contienen X producto"
        if ($this->product_id) {
            $query->whereHas('items', function($q) {
                $q->where('product_id', $this->product_id);
            });
        }
        
        return $query->orderBy('date', 'desc')->get();
    }
    
    public function getSalesByClientData()
    {
        $query = DB::table('v_sales_by_client');
        
        // Nota: Esta vista ya está agrupada, no tiene campo status directo
        // El filtro de estado se aplica en las otras vistas
            
        if ($this->client_id) {
            $query->where('client_id', $this->client_id);
        }
        
        return $query->orderBy('total_purchased', 'desc')->get();
    }
    
    public function getSalesByProductClientData()
    {
        $query = DB::table('v_sales_by_product_client')
            ->where(function($q) {
                $q->whereBetween('last_purchase_date', [$this->date_from, $this->date_to]);
            });
        
        // Nota: Esta vista ya está agrupada, no tiene campo status directo
            
        if ($this->client_id) {
            $query->where('client_id', $this->client_id);
        }
        
        if ($this->product_id) {
            $query->where('product_id', $this->product_id);
        }
        
        return $query->orderBy('total_amount', 'desc')->get();
    }
    
    public function getSalesByPeriodData()
    {
        $query = DB::table('v_sales_by_period')
            ->whereBetween('sale_date', [$this->date_from, $this->date_to]);
        
        if ($this->status) {
            $query->where('status', $this->status);
        }
            
        if ($this->client_id) {
            $query->where('client_id', $this->client_id);
        }
        
        if ($this->product_id) {
            $query->where('product_id', $this->product_id);
        }
        
        return $query->orderBy('sale_date', 'desc')->get();
    }
    
    // Análisis para reporte de período
    public function getPeriodAnalytics()
    {
        $data = $this->getSalesByPeriodData();
        
        return [
            'total_sales' => $data->sum('sale_total'),
            'total_orders' => $data->unique('sale_id')->count(),
            'avg_order_value' => $data->unique('sale_id')->count() > 0 
                ? $data->sum('sale_total') / $data->unique('sale_id')->count() 
                : 0,
            'top_products' => $data->groupBy('product_name')
                ->map(fn($items) => [
                    'name' => $items->first()->product_name,
                    'quantity' => $items->sum('quantity'),
                    'total' => $items->sum('subtotal'),
                ])
                ->sortByDesc('total')
                ->take(10)
                ->values(),
            'top_clients' => $data->groupBy('client_name')
                ->map(fn($items) => [
                    'name' => $items->first()->client_name,
                    'orders' => $items->unique('sale_id')->count(),
                    'total' => $items->sum('sale_total'),
                ])
                ->sortByDesc('total')
                ->take(10)
                ->values(),
        ];
    }
    
    
    public function applyFilters()
    {
        // Obtener datos del formulario
        $data = $this->form->getState();
        
        // Actualizar propiedades con los valores del formulent
        // Livewire automáticamente refrescará la vista cuando cambien estas propiedades
        $this->date_from = $data['date_from'] ?? now()->subMonth()->format('Y-m-d');
        $this->date_to = $data['date_to'] ?? now()->format('Y-m-d');
        $this->status = $data['status'] ?? null;
        $this->client_id = $data['client_id'] ?? null;
        $this->product_id = $data['product_id'] ?? null;
    }
    
    
    public function exportExcelAction(): Action
    {
        return Action::make('exportExcel')
            ->label('Exportar a Excel')
            ->form(function () {
                $reportType = $this->activeTab;
                
                $columns = match($reportType) {
                    'by_order' => [
                        'invoice_number' => 'Número de Pedido',
                        'sale_date' => 'Fecha',
                        'client_name' => 'Cliente',
                        'client_document' => 'Documento Cliente',
                        'city' => 'Ciudad',
                        'sale_total' => 'Total',
                        'status' => 'Estado',
                        'source' => 'Fuente',
                    ],
                    'by_client' => [
                        'client_name' => 'Cliente',
                        'document' => 'Documento',
                        'email' => 'Email',
                        'phone1' => 'Teléfono',
                        'total_orders' => 'Total Pedidos',
                        'total_purchased' => 'Total Comprado',
                        'avg_order_value' => 'Promedio por Pedido',
                        'last_purchase_date' => 'Última Compra',
                        'days_since_last_purchase' => 'Días desde Última Compra',
                    ],
                    'by_product_client' => [
                        'client_name' => 'Cliente',
                        'client_document' => 'Documento Cliente',
                        'product_name' => 'Producto',
                        'sku' => 'SKU',
                        'times_purchased' => 'Veces Comprado',
                        'total_quantity' => 'Cantidad Total',
                        'total_amount' => 'Monto Total',
                        'avg_price' => 'Precio Promedio',
                        'last_purchase_date' => 'Última Compra',
                    ],
                    'by_period' => [
                        'invoice_number' => 'Número de Pedido',
                        'sale_date' => 'Fecha',
                        'client_name' => 'Cliente',
                        'product_name' => 'Producto',
                        'quantity' => 'Cantidad',
                        'price' => 'Precio',
                        'subtotal' => 'Subtotal',
                        'sale_total' => 'Total',
                        'status' => 'Estado',
                        'source' => 'Fuente',
                    ],
                    default => [],
                };
                
                return [
                    CheckboxList::make('columns')
                        ->label('Selecciona las columnas a exportar')
                        ->options($columns)
                        ->default(array_keys($columns))
                        ->columns(2)
                        ->required()
                        ->minItems(1),
                ];
            })
            ->action(function (array $data) {
                $selectedColumns = $data['columns'];
                $reportType = $this->activeTab;
                
                $reportData = match($reportType) {
                    'by_order' => $this->getSalesByOrderData(),
                    'by_client' => $this->getSalesByClientData(),
                    'by_product_client' => $this->getSalesByProductClientData(),
                    'by_period' => $this->getSalesByPeriodData(),
                    default => collect([]),
                };
                
                if ($reportData->isEmpty()) {
                    \Filament\Notifications\Notification::make()
                        ->title('No hay datos para exportar')
                        ->warning()
                        ->send();
                    return;
                }
                
                // Transformar datos según el tipo de reporte
                if ($reportType === 'by_order') {
                    $filteredData = $reportData->map(function($sale) {
                        return [
                            'invoice_number' => $sale->invoice_number,
                            'sale_date' => $sale->date,
                            'client_name' => $sale->client->name,
                            'client_document' => $sale->client->document,
                            'city' => $sale->client->city_name,
                            'sale_total' => $sale->total,
                            'status' => $sale->status,
                            'source' => $sale->source,
                        ];
                    });
                } else {
                    $filteredData = $reportData->map(fn($row) => (array)$row);
                }
                
                // Filtrar solo las columnas seleccionadas
                $filteredData = $filteredData->map(function($row) use ($selectedColumns) {
                    return array_intersect_key($row, array_flip($selectedColumns));
                });
                
                // Obtener labels de columnas
                $columnLabels = $this->getColumnLabels($reportType);
                
                $filename = 'reporte_ventas_' . $reportType . '_' . now()->format('Y-m-d_His') . '.xlsx';
                
                return \Maatwebsite\Excel\Facades\Excel::download(
                    new \App\Exports\SalesReportExport($filteredData, $selectedColumns, $columnLabels),
                    $filename
                );
            });
    }
    
    protected function getColumnLabels($reportType): array
    {
        return match($reportType) {
            'by_order' => [
                'invoice_number' => 'Número de Pedido',
                'sale_date' => 'Fecha',
                'client_name' => 'Cliente',
                'client_document' => 'Documento Cliente',
                'city' => 'Ciudad',
                'sale_total' => 'Total',
                'status' => 'Estado',
                'source' => 'Fuente',
            ],
            'by_client' => [
                'client_name' => 'Cliente',
                'document' => 'Documento',
                'email' => 'Email',
                'phone1' => 'Teléfono',
                'total_orders' => 'Total Pedidos',
                'total_purchased' => 'Total Comprado',
                'avg_order_value' => 'Promedio por Pedido',
                'last_purchase_date' => 'Última Compra',
                'days_since_last_purchase' => 'Días desde Última Compra',
            ],
            'by_product_client' => [
                'client_name' => 'Cliente',
                'client_document' => 'Documento Cliente',
                'product_name' => 'Producto',
                'sku' => 'SKU',
                'times_purchased' => 'Veces Comprado',
                'total_quantity' => 'Cantidad Total',
                'total_amount' => 'Monto Total',
                'avg_price' => 'Precio Promedio',
                'last_purchase_date' => 'Última Compra',
            ],
            'by_period' => [
                'invoice_number' => 'Número de Pedido',
                'sale_date' => 'Fecha',
                'client_name' => 'Cliente',
                'product_name' => 'Producto',
                'quantity' => 'Cantidad',
                'price' => 'Precio',
                'subtotal' => 'Subtotal',
                'sale_total' => 'Total',
                'status' => 'Estado',
                'source' => 'Fuente',
            ],
            default => [],
        };
    }
    
    public function openExportModal($reportType)
    {
        // Ya no se usa, pero lo dejamos para no romper nada
        $this->mountAction('exportExcel');
    }
    
    public function exportToExcel()
    {
        if (empty($this->selectedColumns)) {
            \Filament\Notifications\Notification::make()
                ->title('Selecciona al menos una columna')
                ->warning()
                ->send();
            return;
        }
        
        $data = match($this->exportType) {
            'by_order' => $this->getSalesByOrderData(),
            'by_client' => $this->getSalesByClientData(),
            'by_product_client' => $this->getSalesByProductClientData(),
            'by_period' => $this->getSalesByPeriodData(),
            default => collect([]),
        };
        
        if ($data->isEmpty()) {
            \Filament\Notifications\Notification::make()
                ->title('No hay datos para exportar')
                ->warning()
                ->send();
            return;
        }
        
        // Filtrar solo las columnas seleccionadas
        $filteredData = $data->map(function($row) {
            $row = (array)$row;
            return array_intersect_key($row, array_flip($this->selectedColumns));
        });
        
        // Crear el export usando Maatwebsite/Excel
        $filename = 'reporte_ventas_' . $this->exportType . '_' . now()->format('Y-m-d_His') . '.xlsx';
        
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\SalesReportExport($filteredData, $this->selectedColumns, $this->availableColumns),
            $filename
        );
    }
    
}


