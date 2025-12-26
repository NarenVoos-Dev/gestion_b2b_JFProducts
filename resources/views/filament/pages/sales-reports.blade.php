<x-filament-panels::page>
    {{-- Formulario de Filtros --}}
    <div class="mb-8 p-4 bg-white rounded-lg shadow">
        <form wire:submit.prevent="applyFilters">
            {{ $this->form }}
            
            <div class="mt-4 pt-4">
                <x-filament::button type="submit" color="primary">
                    Aplicar Filtros
                </x-filament::button>
                
                <x-filament::button type="button" color="gray" wire:click="$set('date_from', null); $set('date_to', null); $set('client_id', null); $set('product_id', null); $set('status', null);">
                    Limpiar
                </x-filament::button>
            </div>
        </form>
    </div>

    {{-- Tabs de Reportes --}}
    <x-filament::tabs>
        <x-filament::tabs.item 
            wire:click="$set('activeTab', 'by_order')" 
            :active="$activeTab === 'by_order'"
        >
             Por Pedido
        </x-filament::tabs.item>
        
        <x-filament::tabs.item 
            wire:click="$set('activeTab', 'by_client')" 
            :active="$activeTab === 'by_client'"
        >
             Por Cliente
        </x-filament::tabs.item>
        
        <x-filament::tabs.item 
            wire:click="$set('activeTab', 'by_product_client')" 
            :active="$activeTab === 'by_product_client'"
        >
             Producto x Cliente
        </x-filament::tabs.item>
        
        <x-filament::tabs.item 
            wire:click="$set('activeTab', 'by_period')" 
            :active="$activeTab === 'by_period'"
        >
             Por Período
        </x-filament::tabs.item>
    </x-filament::tabs>

    {{-- Contenido de Tabs --}}
    <div class="mt-6">
        @if($activeTab === 'by_order')
            @include('filament.pages.sales-reports.by-order')
        @elseif($activeTab === 'by_client')
            @include('filament.pages.sales-reports.by-client')
        @elseif($activeTab === 'by_product_client')
            @include('filament.pages.sales-reports.by-product-client')
        @elseif($activeTab === 'by_period')
            @include('filament.pages.sales-reports.by-period')
        @endif
    </div>

    {{-- Renderizar modales de Actions --}}
    <x-filament-actions::modals />
</x-filament-panels::page>
