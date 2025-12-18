<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class B2BDocumentController extends Controller
{
    /**
     * Descargar pedido de venta en PDF
     */
    public function downloadOrder(Request $request, $saleId)
    {
        $user = $request->user();
        
        $sale = Sale::with(['client', 'items.product.unitOfMeasure', 'items.unitOfMeasure', 'items.lots'])
            ->where('id', $saleId)
            ->where('client_id', $user->client_id)
            ->where('source', 'b2b')
            ->firstOrFail();
        
        $pdf = Pdf::loadView('pdf.invoice', ['sale' => $sale]);
        
        return $pdf->download("pedido-{$sale->id}.pdf");
    }
    
    /**
     * Descargar factura PDF (subida por admin)
     */
    public function downloadInvoice(Request $request, $saleId)
    {
        $user = $request->user();
        
        $sale = Sale::where('id', $saleId)
            ->where('client_id', $user->client_id)
            ->where('source', 'b2b')
            ->where('status', 'Facturado')
            ->firstOrFail();
        
        if (!$sale->hasInvoicePdf()) {
            abort(404, 'Factura no disponible');
        }
        
        return Storage::disk('public')->download(
            $sale->invoice_pdf_path,
            "factura-{$sale->invoice_number}.pdf"
        );
    }
    
    /**
     * Ver factura PDF en el navegador (no descargar)
     */
    public function viewInvoice(Request $request, $saleId)
    {
        $user = $request->user();
        
        $sale = Sale::where('id', $saleId)
            ->where('client_id', $user->client_id)
            ->where('source', 'b2b')
            ->where('status', 'Facturado')
            ->firstOrFail();
        
        if (!$sale->hasInvoicePdf()) {
            abort(404, 'Factura no disponible');
        }
        
        // Usar response()->file() para mostrar en navegador en lugar de descargar
        return response()->file(storage_path('app/public/' . $sale->invoice_pdf_path));
    }
    
    /**
     * Imprimir/Ver pedido en formato de recibo
     */
    public function printOrder(Request $request, $saleId)
    {
        $user = $request->user();
        
        $sale = Sale::with(['client', 'items.product.unitOfMeasure', 'items.unitOfMeasure', 'items.lots'])
            ->where('id', $saleId)
            ->where('client_id', $user->client_id)
            ->where('source', 'b2b')
            ->firstOrFail();
        
        // Generar PDF y mostrarlo en el navegador (inline)
        $pdf = Pdf::loadView('pdf.invoice', ['sale' => $sale]);
        
        return $pdf->stream("pedido-{$sale->id}.pdf");
    }
}
