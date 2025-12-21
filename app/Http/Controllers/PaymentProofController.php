<?php

namespace App\Http\Controllers;

use App\Models\AccountPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Response;

class PaymentProofController extends Controller
{
    /**
     * Descargar o visualizar comprobante de pago
     */
    public function download(AccountPayment $payment): Response
    {
        // Verificar que el pago tenga comprobante
        if (!$payment->hasProof()) {
            abort(404, 'Comprobante no encontrado');
        }
        
        // Obtener el path del archivo
        $path = $payment->payment_proof_path;
        
        // Verificar que el archivo existe
        if (!Storage::disk('local')->exists($path)) {
            abort(404, 'Archivo no encontrado');
        }
        
        // Obtener el nombre del archivo original
        $filename = basename($path);
        
        // Determinar el tipo MIME
        $mimeType = Storage::disk('local')->mimeType($path);
        
        // Retornar el archivo para visualización inline (no descarga)
        return response()->make(
            Storage::disk('local')->get($path),
            200,
            [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'inline; filename="' . $filename . '"',
            ]
        );
    }
}
