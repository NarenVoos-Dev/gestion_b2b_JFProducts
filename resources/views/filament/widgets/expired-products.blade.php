<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <span>Productos Vencidos y Próximos a Vencer</span>
            </div>
        </x-slot>
        
        <!-- Tabs -->
        <div class="mb-4 border-b border-gray-200">
            <nav class="-mb-px flex space-x-8">
                <button onclick="showTab('expired')" id="tab-expired" class="tab-button border-b-2 border-red-500 py-2 px-1 text-sm font-medium text-red-600">
                    Vencidos ({{ $totalExpired }})
                </button>
                <button onclick="showTab('expiring')" id="tab-expiring" class="tab-button border-b-2 border-transparent py-2 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    Por Vencer ({{ $totalExpiringSoon }})
                </button>
            </nav>
        </div>

        <!-- Productos Vencidos -->
        <div id="content-expired" class="tab-content">
            @if($totalExpired > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Producto</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Lote</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Vencimiento</th>
                                <th class="px-4 py-3 text-right font-semibold text-gray-700">Stock</th>
                                <th class="px-4 py-3 text-center font-semibold text-gray-700">Días Vencido</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($expiredProducts->take(10) as $lot)
                                @php
                                    $daysExpired = (int) $lot->expiration_date->diffInDays(now(), false);
                                @endphp
                                <tr class="hover:bg-red-50">
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ $lot->product->name ?? 'N/A' }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $lot->lot_number }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $lot->expiration_date->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3 text-right font-bold text-red-600">{{ $lot->quantity }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs font-bold">
                                            {{ $daysExpired }} días
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                @if($totalExpired > 10)
                    <div class="mt-4 pt-4 border-t border-gray-200 text-center text-sm text-gray-500">
                        Mostrando 10 de {{ $totalExpired }} productos vencidos
                    </div>
                @endif
            @else
                <div class="text-center py-8">
                    <svg class="w-16 h-16 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-gray-500 font-medium">No hay productos vencidos con stock</p>
                    <p class="text-sm text-gray-400 mt-1">Todos los productos están vigentes</p>
                </div>
            @endif
        </div>

        <!-- Productos Por Vencer -->
        <div id="content-expiring" class="tab-content hidden">
            @if($totalExpiringSoon > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Producto</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Lote</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Vencimiento</th>
                                <th class="px-4 py-3 text-right font-semibold text-gray-700">Stock</th>
                                <th class="px-4 py-3 text-center font-semibold text-gray-700">Días Restantes</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($expiringSoon->take(10) as $lot)
                                @php
                                    $daysLeft = (int) now()->diffInDays($lot->expiration_date, false);
                                    $badgeColor = $daysLeft <= 7 ? 'bg-orange-100 text-orange-700' : 'bg-yellow-100 text-yellow-700';
                                @endphp
                                <tr class="hover:bg-yellow-50">
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ $lot->product->name ?? 'N/A' }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $lot->lot_number }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $lot->expiration_date->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3 text-right font-bold text-amber-600">{{ $lot->quantity }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="px-2 py-1 {{ $badgeColor }} rounded-full text-xs font-bold">
                                            {{ $daysLeft }} días
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                @if($totalExpiringSoon > 10)
                    <div class="mt-4 pt-4 border-t border-gray-200 text-center text-sm text-gray-500">
                        Mostrando 10 de {{ $totalExpiringSoon }} productos por vencer
                    </div>
                @endif
            @else
                <div class="text-center py-8">
                    <svg class="w-16 h-16 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-gray-500 font-medium">No hay productos próximos a vencer</p>
                    <p class="text-sm text-gray-400 mt-1">En los próximos 30 días</p>
                </div>
            @endif
        </div>
    </x-filament::section>

    <script>
        function showTab(tab) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
            document.querySelectorAll('.tab-button').forEach(btn => {
                btn.classList.remove('border-red-500', 'text-red-600', 'border-yellow-500', 'text-yellow-600');
                btn.classList.add('border-transparent', 'text-gray-500');
            });
            
            document.getElementById('content-' + tab).classList.remove('hidden');
            const activeBtn = document.getElementById('tab-' + tab);
            activeBtn.classList.remove('border-transparent', 'text-gray-500');
            
            if (tab === 'expired') {
                activeBtn.classList.add('border-red-500', 'text-red-600');
            } else {
                activeBtn.classList.add('border-yellow-500', 'text-yellow-600');
            }
        }
    </script>
</x-filament-widgets::widget>
