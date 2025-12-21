<?php

namespace App\Http\Controllers;

use App\Models\AccountPayment;
use App\Models\AccountReceivable;
use Illuminate\Http\Request;

class PaymentApprovalController extends Controller
{
    /**
     * Aprobar un pago pendiente
     */
    public function approve(Request $request, AccountPayment $payment)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|max:50',
            'reference' => 'nullable|string|max:100',
        ]);
        
        // Verificar que el pago esté pendiente (amount = 0)
        if ($payment->amount != 0) {
            return response()->json([
                'success' => false,
                'message' => 'Este pago ya fue aprobado'
            ], 400);
        }
        
        $account = $payment->accountReceivable;
        
        // Verificar que el monto no exceda el saldo
        if ($request->amount > $account->balance) {
            return response()->json([
                'success' => false,
                'message' => 'El monto excede el saldo pendiente de la cuenta'
            ], 400);
        }
        
        // Actualizar el pago
        $payment->update([
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'reference' => $request->reference,
            'notes' => 'Pago aprobado por administrador',
        ]);
        
        // Actualizar balance de la cuenta
        $account->balance -= $request->amount;
        
        // Actualizar estado de la cuenta (solo marcar como pagado si balance es 0 o negativo)
        if ($account->balance <= 0) {
            $account->status = 'paid';
            $account->balance = 0; // Asegurar que quede en 0
        } elseif ($account->balance < $account->amount) {
            $account->status = 'partial';
        }
        
        $account->save();
        
        return redirect()->back()->with('success', 'Pago aprobado exitosamente');
    }
}
