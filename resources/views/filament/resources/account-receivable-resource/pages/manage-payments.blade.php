<x-filament-panels::page>
    <div class="space-y-4">
        <!-- Información de la cuenta -->
        <div class="bg-white rounded-lg shadow p-4">
            <div class="grid grid-cols-5 gap-4">
                <div>
                    <p class="text-sm text-gray-500">Cliente</p>
                    <p class="font-semibold">{{ $record->client->name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">NIT</p>
                    <p class="font-semibold">{{ $record->client->tax_id ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Monto Total</p>
                    <p class="font-semibold text-lg">${{ number_format($record->amount, 0) }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Saldo Pendiente</p>
                    <p class="font-semibold text-lg text-orange-600">${{ number_format($record->balance, 0) }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Estado</p>
                    <x-filament::badge :color="match($record->status) {
                        'paid' => 'success',
                        'partial' => 'warning',
                        'pending' => 'danger',
                        default => 'gray'
                    }">
                        {{ match($record->status) {
                            'paid' => 'Pagado',
                            'partial' => 'Pago Parcial',
                            'pending' => 'Pendiente',
                            'overdue' => 'Vencido',
                            default => $record->status
                        } }}
                    </x-filament::badge>
                </div>
            </div>
        </div>

        <!-- Tabla de pagos -->
        {{ $this->table }}
        
        <!-- Total pagado -->
        <div class="bg-gray-50 rounded-lg p-4">
            <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-gray-700">Total Pagado:</span>
                <span class="text-xl font-bold text-green-600">
                    ${{ number_format($record->payments->sum('amount'), 0) }}
                </span>
            </div>
        </div>
    </div>
</x-filament-panels::page>
