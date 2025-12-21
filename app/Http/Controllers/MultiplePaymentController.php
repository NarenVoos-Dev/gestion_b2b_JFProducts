<?php

namespace App\Http\Controllers;

use App\Models\AccountPayment;
use App\Models\AccountReceivable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MultiplePaymentController extends Controller
{
    /**
     * Subir comprobante de pago para múltiples facturas
     */
    public function uploadMultiplePaymentProof(Request $request)
    {
        $request->validate([
            'account_ids' => 'required|array|min:1',
            'account_ids.*' => 'exists:accounts_receivable,id',
            'payment_amount' => 'required|numeric|min:0.01',
            'distribution_type' => 'required|in:auto,manual',
            'distribution' => 'required_if:distribution_type,manual|array',
            'payment_proof' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);
        
        $user = Auth::user();
        
        if (!$user->client_id) {
            return back()->with('error', 'Usuario no asociado a un cliente');
        }
        
        // Verificar que todas las cuentas pertenezcan al cliente
        $accounts = AccountReceivable::whereIn('id', $request->account_ids)
            ->where('client_id', $user->client_id)
            ->where('status', '!=', 'paid')
            ->get();
        
        if ($accounts->count() !== count($request->account_ids)) {
            return back()->with('error', 'Algunas facturas no son válidas o ya están pagadas');
        }
        
        // Calcular distribución
        if ($request->distribution_type === 'auto') {
            $distribution = $this->calculateAutoDistribution($accounts, $request->payment_amount);
        } else {
            $distribution = $request->distribution;
            
            // Validar distribución manual
            if (!$this->validateManualDistribution($distribution, $accounts, $request->payment_amount)) {
                return back()->with('error', 'La distribución no es válida. Verifica que los montos sean correctos.');
            }
        }
        
        // Guardar comprobante (una sola vez)
        $proofPath = $request->file('payment_proof')->store('payment_proofs', 'local');
        
        $createdPayments = 0;
        
        // Crear AccountPayment para cada factura
        foreach ($distribution as $accountId => $amount) {
            if ($amount > 0) {
                AccountPayment::create([
                    'account_receivable_id' => $accountId,
                    'payment_date' => now(),
                    'amount' => 0, // Pendiente de aprobación
                    'payment_method' => null,
                    'reference' => null,
                    'payment_proof_path' => $proofPath,
                    'notes' => "Pago múltiple - Monto sugerido: $" . number_format($amount, 2),
                ]);
                
                $createdPayments++;
            }
        }
        
        // Obtener información del cliente para notificaciones
        $client = \App\Models\Client::findOrFail($user->client_id);
        
        // Notificaciones desactivadas - solo badge en menú
        // $this->sendAdminNotifications($client, $createdPayments, $accounts);
        
        return back()->with('success', "Pago registrado exitosamente para {$createdPayments} factura(s). Pendiente de aprobación por el administrador.");
    }
    
    /**
     * Enviar notificaciones a administradores
     */
    private function sendAdminNotifications($client, $paymentCount, $accounts)
    {
        $adminUsers = \App\Models\User::role('admin')->get();
        
        $invoiceNumbers = $accounts->pluck('invoice_number')->take(3)->join(', ');
        if ($accounts->count() > 3) {
            $invoiceNumbers .= ' y ' . ($accounts->count() - 3) . ' más';
        }
        
        foreach ($adminUsers as $admin) {
            \Filament\Notifications\Notification::make()
                ->title('Nuevo pago pendiente de aprobación')
                ->body("{$client->name} ha registrado un pago para {$paymentCount} factura(s): {$invoiceNumbers}")
                ->icon('heroicon-o-currency-dollar')
                ->iconColor('warning')
                ->actions([
                    \Filament\Notifications\Actions\Action::make('review')
                        ->label('Revisar Pagos')
                        ->url(route('filament.admin.resources.account-receivables.index'))
                        ->markAsRead()
                ])
                ->sendToDatabase($admin);
        }
    }
    
    /**
     * Calcular distribución automática de pago
     */
    private function calculateAutoDistribution($accounts, $totalAmount)
    {
        $distribution = [];
        $remaining = $totalAmount;
        
        // Ordenar por fecha de vencimiento (más antigua primero)
        $sortedAccounts = $accounts->sortBy('due_date');
        
        foreach ($sortedAccounts as $account) {
            if ($remaining <= 0) break;
            
            $toPay = min($account->balance, $remaining);
            $distribution[$account->id] = $toPay;
            $remaining -= $toPay;
        }
        
        return $distribution;
    }
    
    /**
     * Validar distribución manual de pago
     */
    private function validateManualDistribution($distribution, $accounts, $totalAmount)
    {
        $sum = 0;
        
        foreach ($distribution as $accountId => $amount) {
            $amount = floatval($amount);
            $sum += $amount;
            
            // Validar que el monto no sea negativo
            if ($amount < 0) {
                return false;
            }
            
            // Validar que el monto no exceda el saldo de la factura
            $account = $accounts->firstWhere('id', $accountId);
            if (!$account || $amount > $account->balance) {
                return false;
            }
        }
        
        // Validar que la suma coincida con el monto total (tolerancia de 1 centavo)
        if (abs($sum - $totalAmount) > 0.01) {
            return false;
        }
        
        return true;
    }
}
